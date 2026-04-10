<?php

declare(strict_types=1);

namespace App\Game\Challenge;

use App\Game\Persistence\GameRepository;
use Random\Engine\Mt19937;
use Random\Randomizer;
use Tempest\DateTime\DateTime;
use Tempest\DateTime\FormatPattern;

final readonly class ChallengeCommandResolver
{
    public function __construct(
        private GameRepository $games,
        private ChallengeSpawnSelector $selector = new ChallengeSpawnSelector(),
    ) {}

    /**
     * @return array{accepted: bool, reason: string, reward: int}
     */
    public function handle(string $gameId, ChallengeCommandRequest $request): array
    {
        $game = $this->games->load($gameId);
        $player = $game->player($request->playerId);

        if ($player->stationId !== $request->stationId) {
            return ['accepted' => false, 'reason' => 'not_at_station', 'reward' => 0];
        }

        $challenge = $this->games->activeChallengeAtStation(
            gameId: $gameId,
            stationId: $request->stationId,
        );

        if ($challenge === null) {
            return ['accepted' => false, 'reason' => 'no_active_challenge', 'reward' => 0];
        }

        $this->games->completeChallenge($challenge['id']);

        $updatedPlayer = $player->earn($challenge['reward']);
        $this->games->updatePlayerState(
            gameId: $gameId,
            playerId: $updatedPlayer->id,
            coins: $updatedPlayer->coins,
            stationId: $updatedPlayer->stationId ?? $request->stationId,
        );

        $effectiveAt = $request->effectiveAt
            ?? DateTime::now()->format(FormatPattern::SQL_DATE_TIME);

        $this->games->appendEvent(
            gameId: $gameId,
            type: 'challenge_completed',
            playerId: $player->id,
            payload: [
                'stationId' => $request->stationId,
                'reward' => $challenge['reward'],
            ],
            effectiveAt: $effectiveAt,
        );

        $this->fillChallengePool($gameId, $game, $effectiveAt);

        return ['accepted' => true, 'reason' => 'accepted', 'reward' => $challenge['reward']];
    }

    public function fillChallengePool(string $gameId, ?\App\Game\Domain\Game $game = null, ?string $effectiveAt = null): void
    {
        $game ??= $this->games->load($gameId);
        $effectiveAt ??= DateTime::now()->format(FormatPattern::SQL_DATE_TIME);

        $cap = max(1, count($game->players) * 3);
        $activeCount = $this->games->activeChallengeCount($gameId);

        if ($activeCount >= $cap) {
            return;
        }

        $seed = $this->seedFrom($gameId, $effectiveAt);
        $randomizer = new Randomizer(new Mt19937($seed));

        while ($activeCount < $cap) {
            $excluded = $this->games->activeChallengeStations($gameId);
            $stationId = $this->selector->pickStation($game, $excluded, $randomizer);

            if ($stationId === null) {
                return;
            }

            $reward = $randomizer->getInt(20, 50);

            $this->games->spawnChallenge(
                gameId: $gameId,
                stationId: $stationId,
                reward: $reward,
            );

            $this->games->appendEvent(
                gameId: $gameId,
                type: 'challenge_spawned',
                playerId: null,
                payload: [
                    'stationId' => $stationId,
                    'reward' => $reward,
                ],
                effectiveAt: $effectiveAt,
            );

            $activeCount++;
        }
    }

    private function seedFrom(string $gameId, string $effectiveAt): int
    {
        return abs(crc32($gameId . '|' . $effectiveAt));
    }
}
