<?php

declare(strict_types=1);

namespace Tests\Testo\Dungeon;

use App\Dungeon\Direction;
use App\Dungeon\Events\RelicCollected;
use App\Dungeon\Point;
use App\Dungeon\Tile;
use Testo\Assert;
use Testo\Test;

/**
 * Unlike the card tests, this one exercises the listener itself, so the events must reach it.
 */
#[Test]
final class PlayerMovementListenerTest extends DungeonTest
{
    protected bool $preventEventHandling = false;

    // -------------------------------------------------------------------------
    // Relic
    // -------------------------------------------------------------------------

    public function moving_onto_relic_tile_collects_the_relic(): void
    {
        $this->dungeon->cheat = true;
        $this->dungeon->addTile(new Tile(new Point(1, 0), isRelic: true));

        $this->dungeon->move(Direction::RIGHT);

        $this->eventBus->assertDispatched(RelicCollected::class, function (RelicCollected $event) {
            Assert::true($event->tile->point->equals(new Point(1, 0)));
            Assert::false($event->tile->isRelic);
        });
    }

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
