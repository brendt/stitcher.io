<?php

declare(strict_types=1);

namespace App\Game\State;

use App\Game\Persistence\GameRepository;

final readonly class GameStateResolver
{
    public function __construct(
        private GameRepository $games,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function resolve(string $gameId, bool $includeTimeline = false): array
    {
        $meta = $this->games->loadMeta($gameId);
        $game = $this->games->load($gameId);
        $coordinates = $this->games->stationCoordinates($gameId);

        $players = array_values(array_map(
            static fn ($player): array => [
                'id' => $player->id,
                'coins' => $player->coins,
                'stationId' => $player->stationId,
            ],
            $game->players,
        ));

        $stations = array_values(array_map(
            static function ($station) use ($coordinates): array {
                $coordinate = $coordinates[$station->id] ?? null;

                return [
                    'id' => $station->id,
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

        $challenges = $this->games->allChallenges($gameId);
        $finalization = $this->games->latestMatchFinalization($gameId);

        $payload = [
            'game' => [
                'id' => $meta['id'],
                'status' => $meta['status'],
                'createdAt' => $meta['created_at'],
            ],
            'players' => $players,
            'stations' => $stations,
            'edges' => $edges,
            'challenges' => $challenges,
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
}
