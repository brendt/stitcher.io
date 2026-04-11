<?php

declare(strict_types=1);

namespace App\Game\State;

use App\Game\Ai\SimpleBotResolver;
use App\Game\Challenge\ChallengeCommandResolver;
use App\Game\Move\MoveCommandResolver;
use App\Game\Persistence\GameRepository;
use Random\Engine\Mt19937;
use Random\Randomizer;

final readonly class GameStateResolver
{
    public function __construct(
        private GameRepository $games,
        private ChallengeCommandResolver $challenges,
        private MoveCommandResolver $moves,
        private SimpleBotResolver $bots,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function resolve(string $gameId, bool $includeTimeline = false, ?string $viewerPlayerId = null): array
    {
        $meta = $this->games->loadMeta($gameId);
        $isActive = ($meta['status'] ?? 'pending') === 'active';

        if ($isActive) {
            $this->moves->processDueMoves($gameId);
        }

        $game = $this->games->load($gameId);
        if ($isActive) {
            $this->challenges->fillPlayerSpecificChallengePool(gameId: $gameId, game: $game);
            try {
                $this->bots->playTurn(gameId: $gameId, game: $game);
            } catch (\Throwable) {
                // Bot failures should not block state payloads for human players.
            }
            $this->moves->processDueMoves($gameId);
            $game = $this->games->load($gameId);
            $this->challenges->fillPlayerSpecificChallengePool(gameId: $gameId, game: $game);
        }

        $coordinates = $this->games->stationCoordinates($gameId);
        $usedStationNames = [];

        $players = array_values(array_map(
            function ($player) use ($gameId): array {
                $pendingMove = $this->games->pendingMoveForPlayer(
                    gameId: $gameId,
                    playerId: $player->id,
                );

                $pendingMovePayload = null;
                if ($pendingMove !== null) {
                    $remainingSeconds = max(0, (int) ceil((strtotime($pendingMove['arrivalAt']) - time())));
                    $pendingMovePayload = [
                        'toStationId' => $pendingMove['toStationId'],
                        'arrivalAt' => $pendingMove['arrivalAt'],
                        'travelTimeSeconds' => $pendingMove['travelTimeSeconds'],
                        'remainingSeconds' => $remainingSeconds,
                    ];
                }

                return [
                    'id' => $player->id,
                    'coins' => $player->coins,
                    'stationId' => $player->stationId,
                    'pendingMove' => $pendingMovePayload,
                ];
            },
            $game->players,
        ));

        $stations = array_values(array_map(
            function ($station) use ($coordinates, $gameId, &$usedStationNames): array {
                $coordinate = $coordinates[$station->id] ?? null;

                return [
                    'id' => $station->id,
                    'name' => $this->stationName(gameId: $gameId, stationId: $station->id, usedNames: $usedStationNames),
                    'ownerId' => $station->ownerId,
                    'topValue' => $station->topValue,
                    'isHub' => $station->isHub,
                    'x' => $coordinate['x'] ?? null,
                    'y' => $coordinate['y'] ?? null,
                    'lineId' => $coordinate['line_id'] ?? null,
                ];
            },
            $game->stations,
        ));

        $edges = array_map(
            static fn ($edge): array => [
                'fromStationId' => $edge->fromStationId,
                'toStationId' => $edge->toStationId,
                'travelTimeSeconds' => $edge->travelTimeSeconds,
                'isExpress' => $edge->isExpress,
            ],
            $game->edges,
        );

        $resolvedViewerPlayerId = $viewerPlayerId;
        if ($resolvedViewerPlayerId === null && $game->players !== []) {
            $resolvedViewerPlayerId = array_key_first($game->players);
        }

        $challenges = $this->games->allChallenges($gameId, viewerPlayerId: $resolvedViewerPlayerId);
        $finalization = $this->games->latestMatchFinalization($gameId);

        $payload = [
            'game' => [
                'id' => $meta['id'],
                'status' => $meta['status'],
                'createdAt' => $meta['created_at'],
                'createdAtUnix' => strtotime($meta['created_at']) ?: null,
                'durationSeconds' => 600,
            ],
            'lobby' => $this->resolveLobby($meta, $game),
            'players' => $players,
            'stations' => $stations,
            'edges' => $edges,
            'challenges' => $challenges,
            'bonuses' => $this->games->activeDoubleCoinBonuses($gameId),
            'score' => [
                'winnerPlayerId' => $finalization['winnerPlayerId'] ?? null,
                'isTie' => $finalization['isTie'] ?? false,
                'tiedPlayerIds' => $finalization['tiedPlayerIds'] ?? [],
                'scores' => $finalization['scores'] ?? [],
            ],
        ];

        if ($includeTimeline) {
            $payload['timeline'] = $this->games->latestEvents($gameId, limit: 30);
        }

        return $payload;
    }

    /**
     * @param array{id: string, status: string, created_at: string} $meta
     * @return array{
     *   isPending: bool,
     *   requiredHumanPlayers: int,
     *   joinedHumanPlayers: int,
     *   remainingHumanPlayers: int,
     *   joinUrl: string
     * }
     */
    private function resolveLobby(array $meta, \App\Game\Domain\Game $game): array
    {
        $requiredHumanPlayers = 0;
        $joinedHumanPlayers = 0;

        foreach ($game->players as $player) {
            if (!str_starts_with($player->id, 'p')) {
                continue;
            }

            $requiredHumanPlayers++;
            if ($player->stationId !== null) {
                $joinedHumanPlayers++;
            }
        }

        return [
            'isPending' => ($meta['status'] ?? 'pending') === 'pending',
            'requiredHumanPlayers' => $requiredHumanPlayers,
            'joinedHumanPlayers' => $joinedHumanPlayers,
            'remainingHumanPlayers' => max(0, $requiredHumanPlayers - $joinedHumanPlayers),
            'joinUrl' => sprintf('/game/%s/join', $meta['id']),
        ];
    }

    /**
     * @param array<string, true> $usedNames
     */
    private function stationName(string $gameId, string $stationId, array &$usedNames): string
    {
        $seed = abs(crc32(sprintf('%s|%s', $gameId, $stationId)));
        $random = new Randomizer(new Mt19937($seed));
        $prefixes = ['Bra', 'Cal', 'Dor', 'Eld', 'Fal', 'Glen', 'Har', 'Kel', 'Lor', 'Mar', 'Nor', 'Or', 'Pel', 'Quin', 'Riv', 'Sol', 'Tor', 'Val', 'Wen', 'Yor'];
        $cores = ['an', 'en', 'in', 'or', 'ar', 'el', 'il', 'un', 'yr', 'os', 'ath', 'eth', 'ion', 'ora', 'wyn'];
        $suffixes = ['bridge', 'ford', 'mouth', 'haven', 'field', 'crest', 'shire', 'point', 'gate', 'brook', 'vale', 'ridge', 'cross', 'mark', 'wick'];

        for ($attempt = 0; $attempt < 8; $attempt++) {
            $name = sprintf(
                '%s%s%s',
                $prefixes[$random->getInt(0, count($prefixes) - 1)],
                $cores[$random->getInt(0, count($cores) - 1)],
                $suffixes[$random->getInt(0, count($suffixes) - 1)],
            );

            if (!isset($usedNames[$name])) {
                $usedNames[$name] = true;
                return $name;
            }
        }

        $fallback = sprintf('Station-%s', substr(md5($stationId), 0, 5));
        $usedNames[$fallback] = true;

        return $fallback;
    }
}
