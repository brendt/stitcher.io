<?php

declare(strict_types=1);

namespace Tests\Testo\Dungeon\Cards;

use App\Dungeon\Cards\StabilityPerTilePermanent;
use App\Dungeon\Events\CardUpdated;
use App\Dungeon\Events\PlayerMoved;
use App\Dungeon\Events\PlayerStabilityIncreased;
use App\Dungeon\Events\TileGenerated;
use App\Dungeon\Point;
use App\Dungeon\Tile;
use Testo\Assert;
use Testo\Test;
use Tests\Testo\Dungeon\DungeonTest;

#[Test]
final class StabilityPerTilePermanentTest extends DungeonTest
{
    public function handle_grants_1_stability_per_tile_generated(): void
    {
        $this->dungeon->stability = 50;
        $card = new StabilityPerTilePermanent();
        $tile = new Tile(new Point(5, 5));

        $card->handle($this->dungeon, new TileGenerated($tile));

        Assert::same($this->dungeon->stability, 51);
        $this->eventBus->assertDispatched(PlayerStabilityIncreased::class, function (PlayerStabilityIncreased $event) {
            Assert::same($event->amount, 1);
        });
        $this->eventBus->assertDispatched(CardUpdated::class);
    }

    public function handle_ignores_unrelated_events(): void
    {
        $this->dungeon->stability = 50;
        $card = new StabilityPerTilePermanent();

        $card->handle($this->dungeon, new PlayerMoved(
            from: new Point(0, 0),
            to: new Point(1, 0),
        ));

        Assert::same($this->dungeon->stability, 50);
        $this->eventBus->assertNotDispatched(CardUpdated::class);
    }
}
