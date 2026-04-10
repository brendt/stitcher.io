<?php

declare(strict_types=1);

namespace App\Game\Score;

use App\Game\Persistence\GameRepository;
use Tempest\DateTime\DateTime;
use Tempest\DateTime\FormatPattern;

final readonly class FinalizeMatchResolver
{
    public function __construct(
        private GameRepository $games,
    ) {}

    /**
     * @return array{
     *   accepted: bool,
     *   reason: string,
     *   isTie: bool,
     *   winnerPlayerId: ?string,
     *   scores: array<string, array{stations: int, hubs: int, score: int}>,
     *   tiedPlayerIds: list<string>
     * }
     */
    public function handle(string $gameId, FinalizeMatchRequest $request): array
    {
        $durationSeconds = $request->durationSeconds ?? 600;
        $hubBonus = $request->hubBonus ?? 2;
        $force = $request->force ?? false;

        $meta = $this->games->loadMeta($gameId);
        $now = DateTime::parse($request->effectiveAt ?? 'now');
        $startedAt = DateTime::parse($meta['created_at']);

        $elapsedSeconds = (int) $now->since($startedAt)->getTotalSeconds();

        if (! $force && $elapsedSeconds < $durationSeconds) {
            return [
                'accepted' => false,
                'reason' => 'match_not_elapsed',
                'isTie' => false,
                'winnerPlayerId' => null,
                'scores' => [],
                'tiedPlayerIds' => [],
            ];
        }

        $game = $this->games->load($gameId);

        $scores = [];

        foreach ($game->players as $player) {
            $scores[$player->id] = [
                'stations' => 0,
                'hubs' => 0,
                'score' => 0,
            ];
        }

        foreach ($game->stations as $station) {
            if ($station->ownerId === null) {
                continue;
            }

            $scores[$station->ownerId]['stations']++;

            if ($station->isHub) {
                $scores[$station->ownerId]['hubs']++;
            }
        }

        foreach ($scores as $playerId => $breakdown) {
            $scores[$playerId]['score'] = $breakdown['stations'] + ($breakdown['hubs'] * $hubBonus);
        }

        $ranking = [];

        foreach ($scores as $playerId => $breakdown) {
            $ranking[] = [
                'playerId' => $playerId,
                'score' => $breakdown['score'],
                'stations' => $breakdown['stations'],
            ];
        }

        usort($ranking, static function (array $a, array $b): int {
            if ($a['score'] !== $b['score']) {
                return $b['score'] <=> $a['score'];
            }

            if ($a['stations'] !== $b['stations']) {
                return $b['stations'] <=> $a['stations'];
            }

            return $a['playerId'] <=> $b['playerId'];
        });

        $topScore = $ranking[0]['score'] ?? 0;
        $topStations = $ranking[0]['stations'] ?? 0;

        $tiedPlayerIds = array_values(array_map(
            static fn (array $entry): string => $entry['playerId'],
            array_filter($ranking, static fn (array $entry): bool => $entry['score'] === $topScore && $entry['stations'] === $topStations),
        ));

        $isTie = count($tiedPlayerIds) > 1;
        $winnerPlayerId = $isTie ? null : ($ranking[0]['playerId'] ?? null);

        $effectiveAt = $now->format(FormatPattern::SQL_DATE_TIME);

        $this->games->appendEvent(
            gameId: $gameId,
            type: 'match_finalized',
            playerId: $winnerPlayerId,
            payload: [
                'winnerPlayerId' => $winnerPlayerId,
                'isTie' => $isTie,
                'tiedPlayerIds' => $tiedPlayerIds,
                'hubBonus' => $hubBonus,
                'durationSeconds' => $durationSeconds,
                'scores' => $scores,
            ],
            effectiveAt: $effectiveAt,
        );

        $this->games->setStatus($gameId, 'completed');

        return [
            'accepted' => true,
            'reason' => 'accepted',
            'isTie' => $isTie,
            'winnerPlayerId' => $winnerPlayerId,
            'scores' => $scores,
            'tiedPlayerIds' => $tiedPlayerIds,
        ];
    }
}
