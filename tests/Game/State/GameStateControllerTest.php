<?php

declare(strict_types=1);

namespace Tests\Game\State;

use App\Game\Challenge\ChallengeCommandResolver;
use App\Game\Domain\Edge;
use App\Game\Domain\Game;
use App\Game\Domain\Player;
use App\Game\Domain\Station;
use App\Game\Persistence\GameRepository;
use PHPUnit\Framework\Attributes\Test;
use Tests\IntegrationTestCase;

final class GameStateControllerTest extends IntegrationTestCase
{
    #[Test]
    public function it_returns_ui_ready_game_state_payload(): void
    {
        $gameId = 'game-state-' . random_int(1000, 999999);

        $game = new Game(
            id: $gameId,
            players: [
                'p1' => new Player(id: 'p1', coins: 50, stationId: 'S1'),
                'p2' => new Player(id: 'p2', coins: 40, stationId: 'S2'),
            ],
            stations: [
                'S1' => new Station(id: 'S1', ownerId: 'p1', topValue: 1, isHub: true),
                'S2' => new Station(id: 'S2', ownerId: 'p2', topValue: 1),
                'S3' => new Station(id: 'S3'),
                'S4' => new Station(id: 'S4'),
                'S5' => new Station(id: 'S5'),
                'S6' => new Station(id: 'S6'),
            ],
            edges: [
                new Edge(fromStationId: 'S1', toStationId: 'S2', travelTimeSeconds: 2),
                new Edge(fromStationId: 'S2', toStationId: 'S3', travelTimeSeconds: 1, isExpress: true),
            ],
        );

        $repository = new GameRepository();
        $repository->save(game: $game, seed: 2026, status: 'active');

        (new ChallengeCommandResolver($repository))->fillChallengePool(gameId: $gameId);

        $response = $this->http->get("/games/{$gameId}/state");

        $response->assertOk();

        self::assertSame($gameId, $response->body['game']['id']);
        self::assertSame('active', $response->body['game']['status']);

        self::assertCount(2, $response->body['players']);
        self::assertCount(6, $response->body['stations']);
        self::assertCount(2, $response->body['edges']);
        self::assertCount(6, array_values(array_filter($response->body['challenges'], static fn (array $challenge): bool => $challenge['active'])));

        self::assertArrayNotHasKey('timeline', $response->body);
    }

    #[Test]
    public function it_can_include_timeline_events_when_requested(): void
    {
        $gameId = 'game-state-' . random_int(1000, 999999);

        $game = new Game(
            id: $gameId,
            players: [
                'p1' => new Player(id: 'p1', coins: 40, stationId: 'S1'),
            ],
            stations: [
                'S1' => new Station(id: 'S1', ownerId: 'p1', topValue: 1),
                'S2' => new Station(id: 'S2'),
                'S3' => new Station(id: 'S3'),
            ],
            edges: [
                new Edge(fromStationId: 'S1', toStationId: 'S2', travelTimeSeconds: 2),
            ],
        );

        $repository = new GameRepository();
        $repository->save(game: $game, seed: 2026, status: 'active');

        $repository->appendEvent(
            gameId: $gameId,
            type: 'move_requested',
            playerId: 'p1',
            payload: ['fromStationId' => 'S1', 'toStationId' => 'S2'],
        );

        $response = $this->http->get("/games/{$gameId}/state", query: ['timeline' => true]);

        $response->assertOk();

        self::assertArrayHasKey('timeline', $response->body);
        self::assertNotEmpty($response->body['timeline']);
        self::assertSame('move_requested', $response->body['timeline'][0]['type']);
    }
}
