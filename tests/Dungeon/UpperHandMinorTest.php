<?php

namespace Tests\Dungeon;

use App\Dungeon\Cards\UpperHandMinor;
use App\Dungeon\Events\DwellerDespawned;
use App\Dungeon\Events\DwellerSpawned;
use App\Dungeon\Point;
use PHPUnit\Framework\Attributes\Test;

final class UpperHandMinorTest extends DungeonTest
{
    #[Test]
    public function play_despawns_one_visible_dweller(): void
    {
        $this->dungeon->dwellers = [];
        $this->dungeon->spawnDweller(new Point(2, 0));
        $card = new UpperHandMinor();

        $card->play($this->dungeon);

        // One despawned, one spawned — total stays at 1
        $this->assertSame(1, iterator_count($this->dungeon->loopDwellers()));
        $this->eventBus->assertDispatched(DwellerDespawned::class);
    }

    #[Test]
    public function play_spawns_a_new_dweller(): void
    {
        $this->dungeon->dwellers = [];
        $this->dungeon->spawnDweller(new Point(2, 0));
        $card = new UpperHandMinor();

        $card->play($this->dungeon);

        // Total count stays the same: 1 despawned, 1 spawned
        $this->assertSame(1, iterator_count($this->dungeon->loopDwellers()));
        $this->eventBus->assertDispatched(DwellerSpawned::class);
    }

    #[Test]
    public function can_play_returns_true_when_visible_dwellers_exist(): void
    {
        $this->dungeon->dwellers = [];
        $this->dungeon->spawnDweller(new Point(2, 0));
        $card = new UpperHandMinor();

        $this->assertTrue($card->canPlay($this->dungeon));
    }

    #[Test]
    public function can_play_returns_false_when_no_visible_dwellers(): void
    {
        $this->dungeon->dwellers = [];
        $card = new UpperHandMinor();

        $this->assertFalse($card->canPlay($this->dungeon));
    }
}
