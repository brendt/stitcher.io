<?php

namespace Tests\Dungeon\Cards;

use Tests\Dungeon\DungeonTest;

use App\Dungeon\Cards\LocateManaAltar;
use App\Dungeon\Events\TileGenerated;
use App\Dungeon\Point;
use PHPUnit\Framework\Attributes\Test;

final class LocateManaAltarTest extends DungeonTest
{
    #[Test]
    public function play_generates_tile_at_undiscovered_mana_altar(): void
    {
        $altarPoint = new Point(5, 5);
        $this->dungeon->manaAltars[$altarPoint->x][$altarPoint->y] = $altarPoint;
        $card = new LocateManaAltar();

        $card->play($this->dungeon);

        $this->assertNotNull($this->dungeon->tryTile($altarPoint));
        $this->eventBus->assertDispatched(TileGenerated::class, function (TileGenerated $event) use ($altarPoint) {
            $this->assertTrue($event->tile->point->equals($altarPoint));
        });
    }

    #[Test]
    public function play_does_nothing_when_no_mana_altars_exist(): void
    {
        $this->dungeon->manaAltars = [];
        $card = new LocateManaAltar();

        $card->play($this->dungeon);

        $this->eventBus->assertNotDispatched(TileGenerated::class);
    }
}
