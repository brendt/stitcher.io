<?php

namespace Tests\Dungeon;

use App\Dungeon\Cards\UpperHandMajor;
use App\Dungeon\Events\DwellerDespawned;
use App\Dungeon\Events\DwellerSpawned;
use App\Dungeon\Point;
use PHPUnit\Framework\Attributes\Test;

final class UpperHandMajorTest extends DungeonTest
{
    #[Test]
    public function play_despawns_all_visible_dwellers(): void
    {
        $this->dungeon->dwellers = [];
        // Spawn within visibility radius (default 5, player at origin)
        $this->dungeon->spawnDweller(new Point(2, 0));
        $this->dungeon->spawnDweller(new Point(3, 0));
        $card = new UpperHandMajor();

        $card->play($this->dungeon);

        $this->assertNull($this->dungeon->getDweller(new Point(2, 0)));
        $this->assertNull($this->dungeon->getDweller(new Point(3, 0)));
        $this->eventBus->assertDispatched(DwellerDespawned::class);
    }

    #[Test]
    public function play_spawns_a_new_dweller_for_each_despawned(): void
    {
        $this->dungeon->dwellers = [];
        $this->dungeon->spawnDweller(new Point(2, 0));
        $this->dungeon->spawnDweller(new Point(3, 0));
        $dwellerCountBefore = iterator_count($this->dungeon->loopDwellers());
        $card = new UpperHandMajor();

        $card->play($this->dungeon);

        $this->assertSame($dwellerCountBefore, iterator_count($this->dungeon->loopDwellers()));
        $this->eventBus->assertDispatched(DwellerSpawned::class);
    }

    #[Test]
    public function can_play_returns_true_when_visible_dwellers_exist(): void
    {
        $this->dungeon->dwellers = [];
        $this->dungeon->spawnDweller(new Point(2, 0));
        $card = new UpperHandMajor();

        $this->assertTrue($card->canPlay($this->dungeon));
    }

    #[Test]
    public function can_play_returns_false_when_no_visible_dwellers(): void
    {
        $this->dungeon->dwellers = [];
        $card = new UpperHandMajor();

        $this->assertFalse($card->canPlay($this->dungeon));
    }
}
