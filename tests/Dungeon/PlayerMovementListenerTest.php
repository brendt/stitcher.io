<?php

namespace Tests\Dungeon;

use App\Dungeon\Direction;
use App\Dungeon\Events\RelicCollected;
use App\Dungeon\Point;
use App\Dungeon\Tile;
use Tempest\Testing\Before;
use Tempest\Testing\Test;
use Tempest\Container\Container;

final class PlayerMovementListenerTest extends DungeonTest
{
    #[Before]
    public function setUp(Container $container): void
    {
        $this->setUpDungeon($container, preventEventHandling: false);
    }

    // -------------------------------------------------------------------------
    // Relic
    // -------------------------------------------------------------------------

    #[Test]
    public function moving_onto_relic_tile_collects_the_relic(): void
    {
        $this->dungeon->cheat = true;
        $this->dungeon->addTile(new Tile(new Point(1, 0), isRelic: true));

        $this->dungeon->move(Direction::RIGHT);

        $this->eventBus->assertDispatched(RelicCollected::class, function (RelicCollected $event) {
            $this->assertTrue($event->tile->point->equals(new Point(1, 0)));
            $this->assertFalse($event->tile->isRelic);
        });
    }

    #[Test]
    public function moving_onto_non_relic_tile_does_not_dispatch_relic_collected(): void
    {
        $this->dungeon->cheat = true;
        $this->dungeon->addTile(new Tile(new Point(1, 0)));

        $this->dungeon->move(Direction::RIGHT);

        $this->eventBus->assertNotDispatched(RelicCollected::class);
    }

    // -------------------------------------------------------------------------
    // Mana altar
    // -------------------------------------------------------------------------
}
