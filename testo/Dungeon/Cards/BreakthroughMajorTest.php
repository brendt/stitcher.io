<?php

declare(strict_types=1);

namespace Tests\Testo\Dungeon\Cards;

use App\Dungeon\Cards\BreakthroughMajor;
use App\Dungeon\Events\ActiveCardUnset;
use App\Dungeon\Events\CardUpdated;
use App\Dungeon\Events\PlayerStabilityDecreased;
use App\Dungeon\Events\TileUpdated;
use App\Dungeon\Point;
use App\Dungeon\Tile;
use Testo\Assert;
use Testo\Test;
use Tests\Testo\Dungeon\DungeonTest;

#[Test]
final class BreakthroughMajorTest extends DungeonTest
{
    public function interact_with_tile_removes_walls_and_decreases_stability(): void
    {
        $card = new BreakthroughMajor();
        $tile = new Tile(new Point(1, 0));
        $this->dungeon->addTile($tile);
        $this->dungeon->stability = 80;

        $card->interactWithTile($this->dungeon, $tile);

        Assert::same($this->dungeon->stability, 70);
        Assert::same($card->count, 2);
        $this->eventBus->assertDispatched(TileUpdated::class);
        $this->eventBus->assertDispatched(PlayerStabilityDecreased::class, function (PlayerStabilityDecreased $event) {
            Assert::same($event->amount, 10);
        });
        $this->eventBus->assertDispatched(CardUpdated::class);
    }

    public function interact_with_tile_unsets_active_card_when_count_reaches_zero(): void
    {
        $card = new BreakthroughMajor();
        $card->count = 1;
        $this->dungeon->setActiveCard($card);
        $tile = new Tile(new Point(1, 0));
        $this->dungeon->addTile($tile);

        $card->interactWithTile($this->dungeon, $tile);

        Assert::same($card->count, 0);
        Assert::null($this->dungeon->activeCard);
        $this->eventBus->assertDispatched(ActiveCardUnset::class);
    }

    public function interact_with_tile_does_not_unset_active_card_while_count_is_above_zero(): void
    {
        $card = new BreakthroughMajor(); // count = 3
        $this->dungeon->setActiveCard($card);
        $tile = new Tile(new Point(1, 0));
        $this->dungeon->addTile($tile);

        $card->interactWithTile($this->dungeon, $tile);

        Assert::notNull($this->dungeon->activeCard);
        $this->eventBus->assertNotDispatched(ActiveCardUnset::class);
    }

    public function can_interact_with_tile_returns_false_for_collapsed_tile(): void
    {
        $card = new BreakthroughMajor();
        $tile = new Tile(new Point(1, 0), isCollapsed: true);

        Assert::false($card->canInteractWithTile($this->dungeon, $tile));
    }

    public function can_interact_with_tile_returns_true_for_normal_tile(): void
    {
        $card = new BreakthroughMajor();
        $tile = new Tile(new Point(1, 0));

        Assert::true($card->canInteractWithTile($this->dungeon, $tile));
    }
}
