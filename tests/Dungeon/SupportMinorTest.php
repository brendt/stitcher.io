<?php

namespace Tests\Dungeon;

use App\Dungeon\Cards\SupportMinor;
use App\Dungeon\Events\ActiveCardUnset;
use App\Dungeon\Events\CardUpdated;
use App\Dungeon\Events\TileUpdated;
use App\Dungeon\Point;
use App\Dungeon\Tile;
use PHPUnit\Framework\Attributes\Test;

final class SupportMinorTest extends DungeonTest
{
    #[Test]
    public function interact_with_tile_marks_tile_as_supported(): void
    {
        $card = new SupportMinor();
        $tile = new Tile(new Point(1, 0));
        $this->dungeon->addTile($tile);

        $card->interactWithTile($this->dungeon, $tile);

        $this->assertTrue($tile->isSupported);
        $this->assertSame(9, $card->count);
        $this->eventBus->assertDispatched(TileUpdated::class);
        $this->eventBus->assertDispatched(CardUpdated::class);
    }

    #[Test]
    public function interact_with_tile_unsets_active_card_when_count_reaches_zero(): void
    {
        $card = new SupportMinor();
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
    public function can_interact_with_tile_returns_false_for_trapped_tile(): void
    {
        $card = new SupportMinor();
        $tile = new Tile(new Point(1, 0), isTrapped: true);

        $this->assertFalse($card->canInteractWithTile($this->dungeon, $tile));
    }

    #[Test]
    public function can_interact_with_tile_returns_true_for_normal_tile(): void
    {
        $card = new SupportMinor();
        $tile = new Tile(new Point(1, 0));

        $this->assertTrue($card->canInteractWithTile($this->dungeon, $tile));
    }
}
