<?php

declare(strict_types=1);

namespace Tests\Testo\Dungeon\Cards;

use App\Dungeon\Cards\LocateManaAltar;
use App\Dungeon\Events\TileGenerated;
use App\Dungeon\Point;
use Testo\Assert;
use Testo\Test;
use Tests\Testo\Dungeon\DungeonTest;

#[Test]
final class LocateManaAltarTest extends DungeonTest
{
    public function play_generates_tile_at_undiscovered_mana_altar(): void
    {
        $altarPoint = new Point(5, 5);
        $this->dungeon->manaAltars[$altarPoint->x][$altarPoint->y] = $altarPoint;
        $card = new LocateManaAltar();

        $card->play($this->dungeon);

        Assert::notNull($this->dungeon->tryTile($altarPoint));
        $this->eventBus->assertDispatched(TileGenerated::class, function (TileGenerated $event) use ($altarPoint) {
            Assert::true($event->tile->point->equals($altarPoint));
        });
    }

    public function play_does_nothing_when_no_mana_altars_exist(): void
    {
        $this->dungeon->manaAltars = [];
        $card = new LocateManaAltar();

        $card->play($this->dungeon);

        $this->eventBus->assertNotDispatched(TileGenerated::class);
    }
}
