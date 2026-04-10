<?php

declare(strict_types=1);

namespace Tests\Game\Score;

use App\Game\Domain\Game;
use App\Game\Domain\Player;
use App\Game\Domain\Station;
use App\Game\Persistence\GameRepository;
use PHPUnit\Framework\Attributes\Test;
use Tests\IntegrationTestCase;
use function Tempest\Database\query;

final class FinalizeMatchControllerTest extends IntegrationTestCase
{
    #[Test]
    public function it_rejects_when_match_duration_not_elapsed(): void
    {
        $gameId = 'game-finalize-' . random_int(1000, 999999);

        $game = new Game(
            id: $gameId,
            players: [
                'p1' => new Player(id: 'p1', coins: 40, stationId: 'S1'),
                'p2' => new Player(id: 'p2', coins: 40, stationId: 'S2'),
            ],
            stations: [
                'S1' => new Station(id: 'S1', ownerId: 'p1', topValue: 1),
                'S2' => new Station(id: 'S2', ownerId: 'p2', topValue: 1),
            ],
            edges: [],
        );

        (new GameRepository())->save(game: $game, seed: 2026);

        $response = $this->http->post("/games/{$gameId}/commands/finalize-match", [
            'durationSeconds' => 1200,
            'effectiveAt' => '2026-04-09 12:00:00',
        ]);

        $response->assertOk();

        self::assertFalse($response->body['accepted']);
        self::assertSame('match_not_elapsed', $response->body['reason']);
    }

    #[Test]
    public function it_finalizes_winner_with_station_and_hub_bonus_score(): void
    {
        $gameId = 'game-finalize-' . random_int(1000, 999999);

        $game = new Game(
            id: $gameId,
            players: [
                'p1' => new Player(id: 'p1', coins: 40, stationId: 'S1'),
                'p2' => new Player(id: 'p2', coins: 40, stationId: 'S3'),
            ],
            stations: [
                'S1' => new Station(id: 'S1', ownerId: 'p1', topValue: 1, isHub: true),
                'S2' => new Station(id: 'S2', ownerId: 'p1', topValue: 1),
                'S3' => new Station(id: 'S3', ownerId: 'p2', topValue: 1),
            ],
            edges: [],
        );

        $repository = new GameRepository();
        $repository->save(game: $game, seed: 2026);

        query('games')
            ->update(created_at: '2026-04-09 11:00:00')
            ->where('id = ?', $gameId)
            ->execute();

        $response = $this->http->post("/games/{$gameId}/commands/finalize-match", [
            'durationSeconds' => 60,
            'hubBonus' => 2,
            'effectiveAt' => '2026-04-09 12:00:00',
        ]);

        $response->assertOk();

        self::assertTrue($response->body['accepted']);
        self::assertFalse($response->body['isTie']);
        self::assertSame('p1', $response->body['winnerPlayerId']);
        self::assertSame(4, $response->body['scores']['p1']['score']);
        self::assertSame(1, $response->body['scores']['p2']['score']);

        $this->database->assertTableHasRow(
            table: 'games',
            id: $gameId,
            status: 'completed',
        );
    }

    #[Test]
    public function it_detects_tie_when_score_and_station_count_match(): void
    {
        $gameId = 'game-finalize-' . random_int(1000, 999999);

        $game = new Game(
            id: $gameId,
            players: [
                'p1' => new Player(id: 'p1', coins: 40, stationId: 'S1'),
                'p2' => new Player(id: 'p2', coins: 40, stationId: 'S2'),
            ],
            stations: [
                'S1' => new Station(id: 'S1', ownerId: 'p1', topValue: 1),
                'S2' => new Station(id: 'S2', ownerId: 'p2', topValue: 1),
            ],
            edges: [],
        );

        $repository = new GameRepository();
        $repository->save(game: $game, seed: 2026);

        query('games')
            ->update(created_at: '2026-04-09 10:00:00')
            ->where('id = ?', $gameId)
            ->execute();

        $response = $this->http->post("/games/{$gameId}/commands/finalize-match", [
            'durationSeconds' => 60,
            'hubBonus' => 2,
            'effectiveAt' => '2026-04-09 12:00:00',
        ]);

        $response->assertOk();

        self::assertTrue($response->body['accepted']);
        self::assertTrue($response->body['isTie']);
        self::assertNull($response->body['winnerPlayerId']);
        self::assertSame(['p1', 'p2'], $response->body['tiedPlayerIds']);
    }
}
