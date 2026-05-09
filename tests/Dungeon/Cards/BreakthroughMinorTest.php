<?php

namespace Tests\Dungeon\Cards;

use Tests\Dungeon\DungeonTest;

use App\Dungeon\Cards\BreakthroughMinor;
use App\Dungeon\Events\ActiveCardUnset;
use App\Dungeon\Events\CardUpdated;
use App\Dungeon\Events\PlayerStabilityDecreased;
use App\Dungeon\Events\TileUpdated;
use App\Dungeon\Point;
use App\Dungeon\Tile;
use PHPUnit\Framework\Attributes\Test;

final class BreakthroughMinorTest extends DungeonTest
{
    #[Test]
    public function interact_with_tile_removes_walls_decreases_stability_and_unsets_active_card(): void
    {
        $card = new BreakthroughMinor();
        $this->dungeon->setActiveCard($card);
        $tile = new Tile(new Point(1, 0));
        $this->dungeon->addTile($tile);
        $this->dungeon->stability = 80;

        $card->interactWithTile($this->dungeon, $tile);

        $this->assertSame(60, $this->dungeon->stability);
        $this->assertNull($this->dungeon->activeCard);
        $this->eventBus->assertDispatched(TileUpdated::class);
        $this->eventBus->assertDispatched(PlayerStabilityDecreased::class, function (PlayerStabilityDecreased $event) {
            $this->assertSame(20, $event->amount);
        });
        $this->eventBus->assertDispatched(ActiveCardUnset::class);
        $this->eventBus->assertDispatched(CardUpdated::class);
    }

    #[Test]
    public function can_interact_with_tile_returns_false_for_collapsed_tile(): void
    {
        $card = new BreakthroughMinor();
        $tile = new Tile(new Point(1, 0), isCollapsed: true);

        $this->assertFalse($card->canInteractWithTile($this->dungeon, $tile));
    }

    #[Test]
    public function can_interact_with_tile_returns_true_for_normal_tile(): void
    {
        $card = new BreakthroughMinor();
        $tile = new Tile(new Point(1, 0));

        $this->assertTrue($card->canInteractWithTile($this->dungeon, $tile));
    }
}
