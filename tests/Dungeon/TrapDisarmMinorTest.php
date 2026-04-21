<?php

namespace Tests\Dungeon;

use App\Dungeon\Cards\TrapDisarmMinor;
use App\Dungeon\Events\ActiveCardUnset;
use App\Dungeon\Events\CardUpdated;
use App\Dungeon\Events\TileUpdated;
use App\Dungeon\Point;
use App\Dungeon\Tile;
use PHPUnit\Framework\Attributes\Test;

final class TrapDisarmMinorTest extends DungeonTest
{
    #[Test]
    public function interact_with_tile_removes_trap_and_unsets_active_card(): void
    {
        $card = new TrapDisarmMinor();
        $this->dungeon->setActiveCard($card);
        $tile = new Tile(new Point(1, 0), isTrapped: true);
        $this->dungeon->addTile($tile);

        $card->interactWithTile($this->dungeon, $tile);

        $this->assertFalse($tile->isTrapped);
        $this->assertNull($this->dungeon->activeCard);
        $this->eventBus->assertDispatched(TileUpdated::class);
        $this->eventBus->assertDispatched(ActiveCardUnset::class);
        $this->eventBus->assertDispatched(CardUpdated::class);
    }

    #[Test]
    public function can_interact_with_tile_returns_true_for_trapped_tile(): void
    {
        $card = new TrapDisarmMinor();
        $tile = new Tile(new Point(1, 0), isTrapped: true);

        $this->assertTrue($card->canInteractWithTile($this->dungeon, $tile));
    }

    #[Test]
    public function can_interact_with_tile_returns_false_for_non_trapped_tile(): void
    {
        $card = new TrapDisarmMinor();
        $tile = new Tile(new Point(1, 0));

        $this->assertFalse($card->canInteractWithTile($this->dungeon, $tile));
    }
}
