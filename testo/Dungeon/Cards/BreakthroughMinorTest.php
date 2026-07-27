<?php

declare(strict_types=1);

namespace Tests\Testo\Dungeon\Cards;

use App\Dungeon\Cards\BreakthroughMinor;
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
final class BreakthroughMinorTest extends DungeonTest
{
    public function interact_with_tile_removes_walls_decreases_stability_and_unsets_active_card(): void
    {
        $card = new BreakthroughMinor();
        $this->dungeon->setActiveCard($card);
        $tile = new Tile(new Point(1, 0));
        $this->dungeon->addTile($tile);
        $this->dungeon->stability = 80;

        $card->interactWithTile($this->dungeon, $tile);

        Assert::same($this->dungeon->stability, 60);
        Assert::null($this->dungeon->activeCard);
        $this->eventBus->assertDispatched(TileUpdated::class);
        $this->eventBus->assertDispatched(PlayerStabilityDecreased::class, function (PlayerStabilityDecreased $event) {
            Assert::same($event->amount, 20);
        });
        $this->eventBus->assertDispatched(ActiveCardUnset::class);
        $this->eventBus->assertDispatched(CardUpdated::class);
    }

    public function can_interact_with_tile_returns_false_for_collapsed_tile(): void
    {
        $card = new BreakthroughMinor();
        $tile = new Tile(new Point(1, 0), isCollapsed: true);

        Assert::false($card->canInteractWithTile($this->dungeon, $tile));
    }

    public function can_interact_with_tile_returns_true_for_normal_tile(): void
    {
        $card = new BreakthroughMinor();
        $tile = new Tile(new Point(1, 0));

        Assert::true($card->canInteractWithTile($this->dungeon, $tile));
    }
}
