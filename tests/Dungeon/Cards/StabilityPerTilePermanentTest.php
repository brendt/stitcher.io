<?php

namespace Tests\Dungeon\Cards;

use Tests\Dungeon\DungeonTest;

use App\Dungeon\Cards\StabilityPerTilePermanent;
use App\Dungeon\Events\CardUpdated;
use App\Dungeon\Events\PlayerStabilityIncreased;
use App\Dungeon\Events\TileGenerated;
use App\Dungeon\Point;
use App\Dungeon\Tile;
use PHPUnit\Framework\Attributes\Test;

final class StabilityPerTilePermanentTest extends DungeonTest
{
    #[Test]
    public function handle_grants_1_stability_per_tile_generated(): void
    {
        $this->dungeon->stability = 50;
        $card = new StabilityPerTilePermanent();
        $tile = new Tile(new Point(5, 5));

        $card->handle($this->dungeon, new TileGenerated($tile));

        $this->assertSame(51, $this->dungeon->stability);
        $this->eventBus->assertDispatched(PlayerStabilityIncreased::class, function (PlayerStabilityIncreased $event) {
            $this->assertSame(1, $event->amount);
        });
        $this->eventBus->assertDispatched(CardUpdated::class);
    }

    #[Test]
    public function handle_ignores_unrelated_events(): void
    {
        $this->dungeon->stability = 50;
        $card = new StabilityPerTilePermanent();

        $card->handle($this->dungeon, new \App\Dungeon\Events\PlayerMoved(
            from: new Point(0, 0),
            to: new Point(1, 0),
        ));

        $this->assertSame(50, $this->dungeon->stability);
        $this->eventBus->assertNotDispatched(CardUpdated::class);
    }
}
