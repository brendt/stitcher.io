<?php

declare(strict_types=1);

namespace Tests\Testo\Dungeon\Cards;

use App\Dungeon\Cards\UpperHandMinor;
use App\Dungeon\Events\DwellerDespawned;
use App\Dungeon\Events\DwellerSpawned;
use App\Dungeon\Point;
use Testo\Assert;
use Testo\Test;
use Tests\Testo\Dungeon\DungeonTest;

#[Test]
final class UpperHandMinorTest extends DungeonTest
{
    public function play_despawns_one_visible_dweller(): void
    {
        $this->dungeon->dwellers = [];
        $this->dungeon->spawnDweller(new Point(2, 0));
        $card = new UpperHandMinor();

        $card->play($this->dungeon);

        // One despawned, one spawned — total stays at 1
        Assert::same(iterator_count($this->dungeon->loopDwellers()), 1);
        $this->eventBus->assertDispatched(DwellerDespawned::class);
    }

    public function play_spawns_a_new_dweller(): void
    {
        $this->dungeon->dwellers = [];
        $this->dungeon->spawnDweller(new Point(2, 0));
        $card = new UpperHandMinor();

        $card->play($this->dungeon);

        // Total count stays the same: 1 despawned, 1 spawned
        Assert::same(iterator_count($this->dungeon->loopDwellers()), 1);
        $this->eventBus->assertDispatched(DwellerSpawned::class);
    }

    public function can_play_returns_true_when_visible_dwellers_exist(): void
    {
        $this->dungeon->dwellers = [];
        $this->dungeon->spawnDweller(new Point(2, 0));
        $card = new UpperHandMinor();

        Assert::true($card->canPlay($this->dungeon));
    }

    public function can_play_returns_false_when_no_visible_dwellers(): void
    {
        $this->dungeon->dwellers = [];
        $card = new UpperHandMinor();

        Assert::false($card->canPlay($this->dungeon));
    }
}
