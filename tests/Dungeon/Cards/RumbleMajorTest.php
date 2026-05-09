<?php

namespace Tests\Dungeon\Cards;

use Tests\Dungeon\DungeonTest;

use App\Dungeon\Cards\RumbleMajor;
use App\Dungeon\Events\ActiveCardUnset;
use App\Dungeon\Events\CardUpdated;
use App\Dungeon\Events\PlayerStabilityDecreased;
use App\Dungeon\Events\TileUpdated;
use App\Dungeon\Point;
use App\Dungeon\Tile;
use PHPUnit\Framework\Attributes\Test;

final class RumbleMajorTest extends DungeonTest
{
    #[Test]
    public function interact_with_tile_removes_collapse_and_decreases_stability(): void
    {
        $card = new RumbleMajor();
        $tile = new Tile(new Point(1, 0), isCollapsed: true);
        $this->dungeon->addTile($tile);
        $this->dungeon->stability = 80;

        $card->interactWithTile($this->dungeon, $tile);

        $this->assertFalse($tile->isCollapsed);
        $this->assertSame(70, $this->dungeon->stability);
        $this->eventBus->assertDispatched(TileUpdated::class);
        $this->eventBus->assertDispatched(PlayerStabilityDecreased::class, function (PlayerStabilityDecreased $event) {
            $this->assertSame(10, $event->amount);
        });
        $this->eventBus->assertDispatched(CardUpdated::class);
    }

    #[Test]
    public function interact_with_tile_unsets_active_card_after_three_uses(): void
    {
        $card = new RumbleMajor();
        $this->dungeon->setActiveCard($card);

        for ($i = 0; $i < 3; $i++) {
            $tile = new Tile(new Point($i + 1, 0), isCollapsed: true);
            $this->dungeon->addTile($tile);
            $card->interactWithTile($this->dungeon, $tile);
        }

        $this->assertNull($this->dungeon->activeCard);
        $this->eventBus->assertDispatched(ActiveCardUnset::class);
    }

    #[Test]
    public function can_interact_with_tile_returns_true_for_collapsed_tile(): void
    {
        $card = new RumbleMajor();
        $tile = new Tile(new Point(1, 0), isCollapsed: true);

        $this->assertTrue($card->canInteractWithTile($this->dungeon, $tile));
    }

    #[Test]
    public function can_interact_with_tile_returns_false_for_normal_tile(): void
    {
        $card = new RumbleMajor();
        $tile = new Tile(new Point(1, 0));

        $this->assertFalse($card->canInteractWithTile($this->dungeon, $tile));
    }
}
