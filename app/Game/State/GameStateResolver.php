<?php

declare(strict_types=1);

namespace App\Game\State;

use App\Game\Persistence\GameRepository;
use Random\Engine\Mt19937;
use Random\Randomizer;

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
        $usedStationNames = [];

        $players = array_values(array_map(
            static fn ($player): array => [
                'id' => $player->id,
                'coins' => $player->coins,
                'stationId' => $player->stationId,
            ],
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
