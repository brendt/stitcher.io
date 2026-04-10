<?php

declare(strict_types=1);

namespace Tests\Game\Challenge;

use App\Game\Challenge\ChallengeSpawnSelector;
use App\Game\Domain\Game;
use App\Game\Domain\Player;
use App\Game\Domain\Station;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Random\Engine\Mt19937;
use Random\Randomizer;

final class ChallengeSpawnSelectorTest extends TestCase
{
    #[Test]
    public function it_biases_away_from_leader_owned_regions(): void
    {
        $game = new Game(
            id: 'g1',
            players: [
                'leader' => new Player(id: 'leader', coins: 40, stationId: 'S1'),
                'trailer' => new Player(id: 'trailer', coins: 40, stationId: 'S5'),
            ],
            stations: [
                'S1' => new Station(id: 'S1', ownerId: 'leader', topValue: 1),
                'S2' => new Station(id: 'S2', ownerId: 'leader', topValue: 1),
                'S3' => new Station(id: 'S3', ownerId: 'leader', topValue: 1),
                'S4' => new Station(id: 'S4', ownerId: 'leader', topValue: 1),
                'S5' => new Station(id: 'S5', ownerId: 'trailer', topValue: 1),
            ],
            edges: [],
        );

        $selector = new ChallengeSpawnSelector();
        $randomizer = new Randomizer(new Mt19937(2026));

        $leaderPicks = 0;
        $trailerPicks = 0;

        for ($i = 0; $i < 250; $i++) {
            $picked = $selector->pickStation($game, excludedStationIds: [], randomizer: $randomizer);

            if ($picked === 'S5') {
                $trailerPicks++;
            } else {
                $leaderPicks++;
            }
        }

        self::assertGreaterThan($leaderPicks, $trailerPicks);
    }
}
