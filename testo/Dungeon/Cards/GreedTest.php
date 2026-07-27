<?php

declare(strict_types=1);

namespace Tests\Testo\Dungeon\Cards;

use App\Dungeon\Cards\Greed;
use App\Dungeon\Events\PlayerStabilityDecreased;
use App\Dungeon\Events\TileCoinsCollected;
use App\Dungeon\Point;
use App\Dungeon\Tile;
use Testo\Assert;
use Testo\Test;
use Tests\Testo\Dungeon\DungeonTest;

#[Test]
final class GreedTest extends DungeonTest
{
    public function play_collects_all_coins_from_tiles(): void
    {
        $tile = new Tile(new Point(1, 0), coins: 10);
        $this->dungeon->addTile($tile);
        $card = new Greed();

        $card->play($this->dungeon);

        Assert::same($this->dungeon->coins, 10);
        $this->eventBus->assertDispatched(TileCoinsCollected::class);
    }

    public function play_decreases_stability_by_20(): void
    {
        $this->dungeon->stability = 80;
        $card = new Greed();

        $card->play($this->dungeon);

        Assert::same($this->dungeon->stability, 60);
        $this->eventBus->assertDispatched(PlayerStabilityDecreased::class, function (PlayerStabilityDecreased $event) {
            Assert::same($event->amount, 20);
        });
    }
}
