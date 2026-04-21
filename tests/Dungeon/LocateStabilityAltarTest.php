<?php

namespace Tests\Dungeon;

use App\Dungeon\Cards\LocateStabilityAltar;
use App\Dungeon\Events\TileGenerated;
use App\Dungeon\Point;
use PHPUnit\Framework\Attributes\Test;

final class LocateStabilityAltarTest extends DungeonTest
{
    #[Test]
    public function play_generates_tile_at_undiscovered_stability_altar(): void
    {
        $altarPoint = new Point(5, 5);
        $this->dungeon->stabilityAltars[$altarPoint->x][$altarPoint->y] = $altarPoint;
        $card = new LocateStabilityAltar();

        $card->play($this->dungeon);

        $this->assertNotNull($this->dungeon->tryTile($altarPoint));
        $this->eventBus->assertDispatched(TileGenerated::class, function (TileGenerated $event) use ($altarPoint) {
            $this->assertTrue($event->tile->point->equals($altarPoint));
        });
    }

    #[Test]
    public function play_does_nothing_when_no_stability_altars_exist(): void
    {
        $this->dungeon->stabilityAltars = [];
        $card = new LocateStabilityAltar();

        $card->play($this->dungeon);

        $this->eventBus->assertNotDispatched(TileGenerated::class);
    }
}
