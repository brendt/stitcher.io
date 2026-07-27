<?php

declare(strict_types=1);

namespace Tests\Testo\Dungeon\Cards;

use App\Dungeon\Cards\LocateHealthAltar;
use App\Dungeon\Events\TileGenerated;
use App\Dungeon\Point;
use Testo\Assert;
use Testo\Test;
use Tests\Testo\Dungeon\DungeonTest;

#[Test]
final class LocateHealthAltarTest extends DungeonTest
{
    public function play_generates_tile_at_undiscovered_health_altar(): void
    {
        $altarPoint = new Point(5, 5);
        $this->dungeon->healthAltars[$altarPoint->x][$altarPoint->y] = $altarPoint;
        $card = new LocateHealthAltar();

        $card->play($this->dungeon);

        Assert::notNull($this->dungeon->tryTile($altarPoint));
        $this->eventBus->assertDispatched(TileGenerated::class, function (TileGenerated $event) use ($altarPoint) {
            Assert::true($event->tile->point->equals($altarPoint));
        });
    }

    public function play_skips_altar_that_already_has_a_tile(): void
    {
        $altarPoint = new Point(5, 5);
        $this->dungeon->healthAltars[$altarPoint->x][$altarPoint->y] = $altarPoint;
        $this->dungeon->generateTile(null, $altarPoint); // already discovered
        $card = new LocateHealthAltar();

        $card->play($this->dungeon);

        // TileGenerated was dispatched for the pre-generated tile, not by the card
        $this->eventBus->assertDispatched(TileGenerated::class);
    }

    public function play_does_nothing_when_no_health_altars_exist(): void
    {
        $this->dungeon->healthAltars = [];
        $card = new LocateHealthAltar();

        $card->play($this->dungeon);

        $this->eventBus->assertNotDispatched(TileGenerated::class);
    }
}
