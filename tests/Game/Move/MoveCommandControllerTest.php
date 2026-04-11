<?php

declare(strict_types=1);

namespace Tests\Game\Move;

use App\Game\Domain\Edge;
use App\Game\Domain\Game;
use App\Game\Domain\Player;
use App\Game\Domain\Station;
use App\Game\Persistence\GameRepository;
use PHPUnit\Framework\Attributes\Test;
use Tests\IntegrationTestCase;

final class MoveCommandControllerTest extends IntegrationTestCase
{
    #[Test]
    public function it_rejects_move_when_target_is_not_adjacent(): void
    {
        $gameId = 'game-move-001-' . random_int(1000, 999999);

        $game = new Game(
            id: $gameId,
            players: [
                'p1' => new Player(id: 'p1', coins: 40, stationId: 'S1'),
            ],
            stations: [
                'S1' => new Station(id: 'S1'),
                'S2' => new Station(id: 'S2'),
                'S3' => new Station(id: 'S3'),
            ],
            edges: [
                new Edge(fromStationId: 'S1', toStationId: 'S2', travelTimeSeconds: 2),
            ],
        );

        (new GameRepository())->save(game: $game, seed: 2026, status: 'active');

        $response = $this->http->post("/games/{$gameId}/commands/move", [
            'playerId' => 'p1',
            'fromStationId' => 'S1',
            'toStationId' => 'S3',
            'deposit' => 1,
            'effectiveAt' => '2026-04-09 10:00:00',
        ]);

        $response->assertOk();

        self::assertIsArray($response->body);
        self::assertFalse($response->body['accepted']);
        self::assertSame('not_adjacent', $response->body['reason']);
    }

    #[Test]
    public function it_resolves_same_target_conflict_by_departure_order(): void
    {
        $gameId = 'game-move-002-' . random_int(1000, 999999);

        $game = new Game(
            id: $gameId,
            players: [
                'p1' => new Player(id: 'p1', coins: 40, stationId: 'S1'),
                'p2' => new Player(id: 'p2', coins: 40, stationId: 'S2'),
            ],
            stations: [
                'S1' => new Station(id: 'S1'),
                'S2' => new Station(id: 'S2'),
                'S3' => new Station(id: 'S3'),
            ],
            edges: [
                new Edge(fromStationId: 'S1', toStationId: 'S3', travelTimeSeconds: 2),
                new Edge(fromStationId: 'S2', toStationId: 'S3', travelTimeSeconds: 2),
            ],
        );

        $repository = new GameRepository();
        $repository->save(game: $game, seed: 2026, status: 'active');

        $effectiveAt = '2026-04-09 11:00:00';

        $first = $this->http->post("/games/{$gameId}/commands/move", [
            'playerId' => 'p1',
            'fromStationId' => 'S1',
            'toStationId' => 'S3',
            'deposit' => 1,
            'effectiveAt' => $effectiveAt,
        ]);

        $second = $this->http->post("/games/{$gameId}/commands/move", [
            'playerId' => 'p2',
            'fromStationId' => 'S2',
            'toStationId' => 'S3',
            'deposit' => 1,
            'effectiveAt' => $effectiveAt,
        ]);

        $first->assertOk();
        $second->assertOk();

        self::assertTrue($first->body['accepted']);
        self::assertSame('accepted', $first->body['reason']);

        self::assertFalse($second->body['accepted']);
        self::assertSame('station_conflict', $second->body['reason']);

        $loaded = $repository->load($gameId);

        self::assertSame('S3', $loaded->players['p1']->stationId);
        self::assertSame(39, $loaded->players['p1']->coins);

        self::assertSame('S2', $loaded->players['p2']->stationId);
        self::assertSame(40, $loaded->players['p2']->coins);

        self::assertSame('p1', $loaded->stations['S3']->ownerId);
        self::assertSame(1, $loaded->stations['S3']->topValue);
    }

    #[Test]
    public function it_doubles_coins_when_landing_on_two_x_bonus_and_removes_it(): void
    {
        $gameId = 'game-move-2x-001-' . random_int(1000, 999999);

        $game = new Game(
            id: $gameId,
            players: [
                'p1' => new Player(id: 'p1', coins: 40, stationId: 'S1'),
            ],
            stations: [
                'S1' => new Station(id: 'S1', ownerId: 'p1', topValue: 1),
                'S2' => new Station(id: 'S2', ownerId: 'p1', topValue: 1),
            ],
            edges: [
                new Edge(fromStationId: 'S1', toStationId: 'S2', travelTimeSeconds: 1),
            ],
        );

        $repository = new GameRepository();
        $repository->save(game: $game, seed: 2026, status: 'active');
        $repository->appendEvent(
            gameId: $gameId,
            type: 'double_coin_spawned',
            playerId: null,
            payload: [
                'stationId' => 'S2',
                'type' => '2x',
            ],
            effectiveAt: '2026-04-09 00:00:00',
        );

        $response = $this->http->post("/games/{$gameId}/commands/move", [
            'playerId' => 'p1',
            'fromStationId' => 'S1',
            'toStationId' => 'S2',
            'effectiveAt' => '2026-04-09 10:00:00',
        ]);

        $response->assertOk();
        self::assertTrue($response->body['accepted']);

        $loaded = $repository->load($gameId);
        self::assertSame('S2', $loaded->players['p1']->stationId);
        self::assertSame(80, $loaded->players['p1']->coins);
        self::assertSame([], $repository->activeDoubleCoinStations($gameId));
    }

    #[Test]
    public function it_spawns_two_x_bonus_on_step_thresholds(): void
    {
        $gameId = 'game-move-2x-002-' . random_int(1000, 999999);

        $game = new Game(
            id: $gameId,
            players: [
                'p1' => new Player(id: 'p1', coins: 40, stationId: 'S1'),
            ],
            stations: [
                'S1' => new Station(id: 'S1', ownerId: 'p1', topValue: 1),
                'S2' => new Station(id: 'S2', ownerId: 'p1', topValue: 1),
                'S3' => new Station(id: 'S3'),
                'S4' => new Station(id: 'S4'),
                'S5' => new Station(id: 'S5'),
            ],
            edges: [
                new Edge(fromStationId: 'S1', toStationId: 'S2', travelTimeSeconds: 1),
                new Edge(fromStationId: 'S2', toStationId: 'S3', travelTimeSeconds: 1),
                new Edge(fromStationId: 'S3', toStationId: 'S4', travelTimeSeconds: 1),
                new Edge(fromStationId: 'S4', toStationId: 'S5', travelTimeSeconds: 1),
            ],
        );

        $repository = new GameRepository();
        $repository->save(game: $game, seed: 2026, status: 'active');

        $from = 'S1';
        $to = 'S2';

        for ($step = 1; $step <= 20; $step++) {
            $response = $this->http->post("/games/{$gameId}/commands/move", [
                'playerId' => 'p1',
                'fromStationId' => $from,
                'toStationId' => $to,
                'effectiveAt' => sprintf('2026-04-09 11:%02d:00', $step),
            ]);

            $response->assertOk();
            self::assertTrue($response->body['accepted']);

            [$from, $to] = [$to, $from];
        }

        self::assertSame(20, $repository->acceptedMoveCount($gameId));
        self::assertSame(1, $repository->doubleCoinSpawnCount($gameId));
        self::assertSame(['S5'], $repository->activeDoubleCoinStations($gameId));
    }
}
