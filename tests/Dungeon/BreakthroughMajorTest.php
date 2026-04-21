<?php

namespace Tests\Dungeon;

use App\Dungeon\Cards\BreakthroughMajor;
use App\Dungeon\Events\ActiveCardUnset;
use App\Dungeon\Events\CardUpdated;
use App\Dungeon\Events\PlayerStabilityDecreased;
use App\Dungeon\Events\TileUpdated;
use App\Dungeon\Point;
use App\Dungeon\Tile;
use PHPUnit\Framework\Attributes\Test;

final class BreakthroughMajorTest extends DungeonTest
{
    #[Test]
    public function interact_with_tile_removes_walls_and_decreases_stability(): void
    {
        $card = new BreakthroughMajor();
        $tile = new Tile(new Point(1, 0));
        $this->dungeon->addTile($tile);
        $this->dungeon->stability = 80;

        $card->interactWithTile($this->dungeon, $tile);

        $this->assertSame(70, $this->dungeon->stability);
        $this->assertSame(2, $card->count);
        $this->eventBus->assertDispatched(TileUpdated::class);
        $this->eventBus->assertDispatched(PlayerStabilityDecreased::class, function (PlayerStabilityDecreased $event) {
            $this->assertSame(10, $event->amount);
        });
        $this->eventBus->assertDispatched(CardUpdated::class);
    }

    #[Test]
    public function interact_with_tile_unsets_active_card_when_count_reaches_zero(): void
    {
        $card = new BreakthroughMajor();
        $card->count = 1;
        $this->dungeon->setActiveCard($card);
        $tile = new Tile(new Point(1, 0));
        $this->dungeon->addTile($tile);

        $card->interactWithTile($this->dungeon, $tile);

        $this->assertSame(0, $card->count);
        $this->assertNull($this->dungeon->activeCard);
        $this->eventBus->assertDispatched(ActiveCardUnset::class);
    }

    #[Test]
    public function interact_with_tile_does_not_unset_active_card_while_count_is_above_zero(): void
    {
        $card = new BreakthroughMajor(); // count = 3
        $this->dungeon->setActiveCard($card);
        $tile = new Tile(new Point(1, 0));
        $this->dungeon->addTile($tile);

        $card->interactWithTile($this->dungeon, $tile);

        $this->assertNotNull($this->dungeon->activeCard);
        $this->eventBus->assertNotDispatched(ActiveCardUnset::class);
    }

    #[Test]
    public function can_interact_with_tile_returns_false_for_collapsed_tile(): void
    {
        $card = new BreakthroughMajor();
        $tile = new Tile(new Point(1, 0), isCollapsed: true);

        $this->assertFalse($card->canInteractWithTile($this->dungeon, $tile));
    }

    #[Test]
    public function can_interact_with_tile_returns_true_for_normal_tile(): void
    {
        $card = new BreakthroughMajor();
        $tile = new Tile(new Point(1, 0));

        $this->assertTrue($card->canInteractWithTile($this->dungeon, $tile));
    }
}
