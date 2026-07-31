<?php

declare(strict_types=1);

namespace Tests\Testo\Dungeon\Cards;

use App\Dungeon\Cards\UpperHandMajor;
use App\Dungeon\Events\DwellerDespawned;
use App\Dungeon\Events\DwellerSpawned;
use App\Dungeon\Point;
use Testo\Assert;
use Testo\Test;
use Tests\Testo\Dungeon\DungeonTest;

#[Test]
final class UpperHandMajorTest extends DungeonTest
{
    public function play_despawns_all_visible_dwellers(): void
    {
        $this->dungeon->dwellers = [];
        // Spawn within visibility radius (default 5, player at origin)
        $this->dungeon->spawnDweller(new Point(2, 0));
        $this->dungeon->spawnDweller(new Point(3, 0));
        $card = new UpperHandMajor();

        $card->play($this->dungeon);

        Assert::null($this->dungeon->getDweller(new Point(2, 0)));
        Assert::null($this->dungeon->getDweller(new Point(3, 0)));
        $this->eventBus->assertDispatched(DwellerDespawned::class);
    }

    public function play_spawns_a_new_dweller_for_each_despawned(): void
    {
        $this->dungeon->dwellers = [];
        $this->dungeon->spawnDweller(new Point(2, 0));
        $this->dungeon->spawnDweller(new Point(3, 0));
        $dwellerCountBefore = iterator_count($this->dungeon->loopDwellers());
        $card = new UpperHandMajor();

        $card->play($this->dungeon);

        Assert::same(iterator_count($this->dungeon->loopDwellers()), $dwellerCountBefore);
        $this->eventBus->assertDispatched(DwellerSpawned::class);
    }

    public function can_play_returns_true_when_visible_dwellers_exist(): void
    {
        $this->dungeon->dwellers = [];
        $this->dungeon->spawnDweller(new Point(2, 0));
        $card = new UpperHandMajor();

        Assert::true($card->canPlay($this->dungeon));
    }

    public function can_play_returns_false_when_no_visible_dwellers(): void
    {
        $this->dungeon->dwellers = [];
        $card = new UpperHandMajor();

        Assert::false($card->canPlay($this->dungeon));
    }
}
