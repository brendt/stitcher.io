<?php

namespace Tests\Dungeon;

use App\Dungeon\Events\TileGenerated;
use App\Dungeon\Point;
use PHPUnit\Framework\Attributes\Test;

final class DungeonActionsTest extends DungeonTest
{
    #[Test]
    public function generate_tile_adds_new_tile(): void
    {
        $point = new Point(x: 1, y: 1);

        $this->dungeon->generateTile(from: null, to: $point);

        $this->assertNotNull($this->dungeon->tryTile($point));

        $this->eventBus->assertDispatched(TileGenerated::class, function (TileGenerated $event) use ($point) {
            $this->assertTrue($event->tile->point->equals($point));
        });
    }
}
