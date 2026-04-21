<?php

namespace Tests\Dungeon;

use App\Dungeon\Cards\ManaPerMovePermanent;
use App\Dungeon\Events\CardUpdated;
use App\Dungeon\Events\PlayerManaIncreased;
use App\Dungeon\Events\PlayerMoved;
use App\Dungeon\Point;
use PHPUnit\Framework\Attributes\Test;

final class ManaPerMovePermanentTest extends DungeonTest
{
    #[Test]
    public function handle_grants_1_mana_per_move(): void
    {
        $card = new ManaPerMovePermanent();
        $this->dungeon->mana = 0;

        $card->handle($this->dungeon, new PlayerMoved(from: new Point(0, 0), to: new Point(1, 0)));

        $this->assertSame(1, $this->dungeon->mana);
        $this->eventBus->assertDispatched(PlayerManaIncreased::class, function (PlayerManaIncreased $event) {
            $this->assertSame(1, $event->amount);
        });
        $this->eventBus->assertDispatched(CardUpdated::class);
    }

    #[Test]
    public function handle_ignores_unrelated_events(): void
    {
        $card = new ManaPerMovePermanent();
        $this->dungeon->mana = 0;

        $card->handle($this->dungeon, new \App\Dungeon\Events\TileGenerated(
            new \App\Dungeon\Tile(new Point(1, 0))
        ));

        $this->assertSame(0, $this->dungeon->mana);
        $this->eventBus->assertNotDispatched(CardUpdated::class);
    }
}
