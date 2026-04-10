<?php

declare(strict_types=1);

namespace Tests\Game\Persistence;

use App\Game\Domain\Game;
use App\Game\Domain\Player;
use App\Game\Map\MapGenerator;
use App\Game\Persistence\GameRepository;
use PHPUnit\Framework\Attributes\Test;
use Tests\IntegrationTestCase;

final class GameRepositoryTest extends IntegrationTestCase
{
    #[Test]
    public function it_persists_and_loads_game_state(): void
    {
        $gameId = 'game-test-' . random_int(1000, 999999);
        $map = (new MapGenerator())->generate(stationCount: 12, seed: 2026);

        $stations = $map->stations;
        $stations['S1'] = $stations['S1']->withClaim(ownerId: 'p1', topValue: 3);

        $game = new Game(
            id: $gameId,
            players: [
                'p1' => new Player(id: 'p1', coins: 40, stationId: 'S1'),
                'p2' => new Player(id: 'p2', coins: 35, stationId: 'S2'),
            ],
            stations: $stations,
            edges: $map->edges,
        );

        $repository = new GameRepository();
        $repository->save(game: $game, seed: 2026);

        $this->database->assertTableHasRow(
            table: 'games',
            id: $gameId,
            seed: 2026,
            status: 'pending',
        );

        $loaded = $repository->load($gameId);

        self::assertCount(2, $loaded->players);
        self::assertSame(40, $loaded->players['p1']->coins);
        self::assertSame('S1', $loaded->players['p1']->stationId);

        self::assertCount(count($map->stations), $loaded->stations);
        self::assertSame('p1', $loaded->stations['S1']->ownerId);
        self::assertSame(3, $loaded->stations['S1']->topValue);

        self::assertCount(count($map->edges), $loaded->edges);
    }

    #[Test]
    public function it_persists_station_coordinates_when_provided(): void
    {
        $gameId = 'game-coords-' . random_int(1000, 999999);
        $map = (new MapGenerator())->generate(stationCount: 12, seed: 1337);

        $game = new Game(
            id: $gameId,
            players: [
                'p1' => new Player(id: 'p1', coins: 40, stationId: 'S1'),
                'p2' => new Player(id: 'p2', coins: 35, stationId: 'S2'),
            ],
            stations: $map->stations,
            edges: $map->edges,
        );

        $repository = new GameRepository();
        $repository->save(
            game: $game,
            seed: 1337,
            stationCoordinates: $map->stationCoordinates,
        );

        $coordinates = $repository->stationCoordinates($gameId);

        self::assertArrayHasKey('S1', $coordinates);
        self::assertSame($map->stationCoordinates['S1']['x'], $coordinates['S1']['x']);
        self::assertSame($map->stationCoordinates['S1']['y'], $coordinates['S1']['y']);
        self::assertSame($map->stationCoordinates['S1']['line_id'], $coordinates['S1']['line_id']);
    }
}
