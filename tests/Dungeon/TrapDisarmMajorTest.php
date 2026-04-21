<?php

namespace Tests\Dungeon;

use App\Dungeon\Cards\TrapDisarmMajor;
use App\Dungeon\Events\ActiveCardUnset;
use App\Dungeon\Events\CardUpdated;
use App\Dungeon\Events\TileUpdated;
use App\Dungeon\Point;
use App\Dungeon\Tile;
use PHPUnit\Framework\Attributes\Test;

final class TrapDisarmMajorTest extends DungeonTest
{
    #[Test]
    public function interact_with_tile_removes_trap(): void
    {
        $card = new TrapDisarmMajor();
        $tile = new Tile(new Point(1, 0), isTrapped: true);
        $this->dungeon->addTile($tile);

        $card->interactWithTile($this->dungeon, $tile);

        $this->assertFalse($tile->isTrapped);
        $this->eventBus->assertDispatched(TileUpdated::class);
        $this->eventBus->assertDispatched(CardUpdated::class);
    }

    #[Test]
    public function interact_with_tile_unsets_active_card_after_three_uses(): void
    {
        $card = new TrapDisarmMajor();
        $this->dungeon->setActiveCard($card);

        for ($i = 0; $i < 3; $i++) {
            $tile = new Tile(new Point($i + 1, 0), isTrapped: true);
            $this->dungeon->addTile($tile);
            $card->interactWithTile($this->dungeon, $tile);
        }

        $this->assertNull($this->dungeon->activeCard);
        $this->eventBus->assertDispatched(ActiveCardUnset::class);
    }

    #[Test]
    public function interact_with_tile_does_not_unset_card_before_three_uses(): void
    {
        $card = new TrapDisarmMajor();
        $this->dungeon->setActiveCard($card);
        $tile = new Tile(new Point(1, 0), isTrapped: true);
        $this->dungeon->addTile($tile);

        $card->interactWithTile($this->dungeon, $tile);

        $this->assertNotNull($this->dungeon->activeCard);
        $this->eventBus->assertNotDispatched(ActiveCardUnset::class);
    }

    #[Test]
    public function can_interact_with_tile_returns_true_for_trapped_tile(): void
    {
        $card = new TrapDisarmMajor();
        $tile = new Tile(new Point(1, 0), isTrapped: true);

        $this->assertTrue($card->canInteractWithTile($this->dungeon, $tile));
    }

    #[Test]
    public function can_interact_with_tile_returns_false_for_non_trapped_tile(): void
    {
        $card = new TrapDisarmMajor();
        $tile = new Tile(new Point(1, 0));

        $this->assertFalse($card->canInteractWithTile($this->dungeon, $tile));
    }
}
