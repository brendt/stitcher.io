<?php

namespace Tests\Dungeon\Cards;

use Tests\Dungeon\DungeonTest;

use App\Dungeon\Cards\Greed;
use App\Dungeon\Events\TileCoinsCollected;
use App\Dungeon\Events\PlayerStabilityDecreased;
use App\Dungeon\Point;
use App\Dungeon\Tile;
use PHPUnit\Framework\Attributes\Test;

final class GreedTest extends DungeonTest
{
    #[Test]
    public function play_collects_all_coins_from_tiles(): void
    {
        $tile = new Tile(new Point(1, 0), coins: 10);
        $this->dungeon->addTile($tile);
        $card = new Greed();

        $card->play($this->dungeon);

        $this->assertSame(10, $this->dungeon->coins);
        $this->eventBus->assertDispatched(TileCoinsCollected::class);
    }

    #[Test]
    public function play_decreases_stability_by_20(): void
    {
        $this->dungeon->stability = 80;
        $card = new Greed();

        $card->play($this->dungeon);

        $this->assertSame(60, $this->dungeon->stability);
        $this->eventBus->assertDispatched(PlayerStabilityDecreased::class, function (PlayerStabilityDecreased $event) {
            $this->assertSame(20, $event->amount);
        });
    }
}
