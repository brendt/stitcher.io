<?php

declare(strict_types=1);

namespace Tests\Game\Challenge;

use App\Game\Challenge\ChallengeCommandResolver;
use App\Game\Domain\Edge;
use App\Game\Domain\Game;
use App\Game\Domain\Player;
use App\Game\Domain\Station;
use App\Game\Persistence\GameRepository;
use PHPUnit\Framework\Attributes\Test;
use Tests\IntegrationTestCase;

final class ChallengeCommandControllerTest extends IntegrationTestCase
{
    #[Test]
    public function challenge_completion_rewards_between_ten_and_twenty_five(): void
    {
        $gameId = 'game-challenge-' . random_int(1000, 999999);
        $game = new Game(
            id: $gameId,
            players: [
                'p1' => new Player(id: 'p1', coins: 40, stationId: 'S1'),
                'p2' => new Player(id: 'p2', coins: 40, stationId: 'S2'),
            ],
            stations: [
                'S1' => new Station(id: 'S1', ownerId: 'p1', topValue: 1),
                'S2' => new Station(id: 'S2', ownerId: 'p2', topValue: 1),
                'S3' => new Station(id: 'S3'),
                'S4' => new Station(id: 'S4'),
                'S5' => new Station(id: 'S5'),
                'S6' => new Station(id: 'S6'),
            ],
            edges: [
                new Edge(fromStationId: 'S1', toStationId: 'S3', travelTimeSeconds: 2),
            ],
        );

        $repository = new GameRepository();
        $repository->save(game: $game, seed: 2026);

        $repository->spawnChallenge(gameId: $gameId, stationId: 'S1', reward: 25);

        $response = $this->http->post("/games/{$gameId}/commands/complete-challenge", [
            'playerId' => 'p1',
            'stationId' => 'S1',
            'effectiveAt' => '2026-04-09 12:00:00',
        ]);

        $response->assertOk();

        self::assertTrue($response->body['accepted']);
        self::assertSame(25, $response->body['reward']);

        $loaded = $repository->load($gameId);
        self::assertSame(65, $loaded->players['p1']->coins);

        $this->database->assertTableHasRow(
            table: 'game_challenges',
            game_id: $gameId,
            station_id: 'S1',
            active: false,
        );

        $rewards = array_map(
            static fn (array $challenge): int => $challenge['reward'],
            $repository->activeChallenges($gameId),
        );

        foreach ($rewards as $reward) {
            self::assertGreaterThanOrEqual(10, $reward);
            self::assertLessThanOrEqual(25, $reward);
        }
    }

    #[Test]
    public function challenge_pool_is_capped_at_three_times_player_count(): void
    {
        $gameId = 'game-cap-' . random_int(1000, 999999);

        $game = new Game(
            id: $gameId,
            players: [
                'p1' => new Player(id: 'p1', coins: 40, stationId: 'S1'),
                'p2' => new Player(id: 'p2', coins: 40, stationId: 'S2'),
            ],
            stations: [
                'S1' => new Station(id: 'S1', ownerId: 'p1', topValue: 1),
                'S2' => new Station(id: 'S2', ownerId: 'p2', topValue: 1),
                'S3' => new Station(id: 'S3'),
                'S4' => new Station(id: 'S4'),
                'S5' => new Station(id: 'S5'),
                'S6' => new Station(id: 'S6'),
                'S7' => new Station(id: 'S7'),
                'S8' => new Station(id: 'S8'),
                'S9' => new Station(id: 'S9'),
                'S10' => new Station(id: 'S10'),
            ],
            edges: [],
        );

        $repository = new GameRepository();
        $repository->save(game: $game, seed: 2026);

        $resolver = new ChallengeCommandResolver($repository);
        $resolver->fillChallengePool(gameId: $gameId);

        self::assertSame(6, $repository->activeChallengeCount($gameId));

        $resolver->fillChallengePool(gameId: $gameId);

        self::assertSame(6, $repository->activeChallengeCount($gameId));
    }
}
