<?php

declare(strict_types=1);

namespace Tests\Testo\Dungeon\Cards;

use App\Dungeon\Cards\SupportMajor;
use App\Dungeon\Events\ActiveCardUnset;
use App\Dungeon\Events\CardUpdated;
use App\Dungeon\Events\TileUpdated;
use App\Dungeon\Point;
use App\Dungeon\Tile;
use Testo\Assert;
use Testo\Test;
use Tests\Testo\Dungeon\DungeonTest;

#[Test]
final class SupportMajorTest extends DungeonTest
{
    public function interact_with_tile_marks_tile_as_supported(): void
    {
        $card = new SupportMajor();
        $tile = new Tile(new Point(1, 0));
        $this->dungeon->addTile($tile);

        $card->interactWithTile($this->dungeon, $tile);

        Assert::true($tile->isSupported);
        Assert::same($card->count, 24);
        $this->eventBus->assertDispatched(TileUpdated::class);
        $this->eventBus->assertDispatched(CardUpdated::class);
    }

    public function interact_with_tile_unsets_active_card_when_count_reaches_zero(): void
    {
        $card = new SupportMajor();
        $card->count = 1;
        $this->dungeon->setActiveCard($card);
        $tile = new Tile(new Point(1, 0));
        $this->dungeon->addTile($tile);

        $card->interactWithTile($this->dungeon, $tile);

        Assert::same($card->count, 0);
        Assert::null($this->dungeon->activeCard);
        $this->eventBus->assertDispatched(ActiveCardUnset::class);
    }

    public function can_interact_with_tile_returns_false_for_collapsed_tile(): void
    {
        $card = new SupportMajor();
        $tile = new Tile(new Point(1, 0), isCollapsed: true);

        Assert::false($card->canInteractWithTile($this->dungeon, $tile));
    }

    public function can_interact_with_tile_returns_false_for_already_supported_tile(): void
    {
        $card = new SupportMajor();
        $tile = new Tile(new Point(1, 0), isSupported: true);

        Assert::false($card->canInteractWithTile($this->dungeon, $tile));
    }

    public function can_interact_with_tile_returns_false_for_origin_tile(): void
    {
        $card = new SupportMajor();
        $tile = new Tile(new Point(0, 0), isOrigin: true);

        Assert::false($card->canInteractWithTile($this->dungeon, $tile));
    }

    public function can_interact_with_tile_returns_true_for_normal_tile(): void
    {
        $card = new SupportMajor();
        $tile = new Tile(new Point(1, 0));

        Assert::true($card->canInteractWithTile($this->dungeon, $tile));
    }
}
