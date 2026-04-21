<?php

namespace Tests\Dungeon\Cards;

use Tests\Dungeon\DungeonTest;

use App\Dungeon\Cards\KillDwellerMinor;
use App\Dungeon\Events\ActiveCardUnset;
use App\Dungeon\Events\CardUpdated;
use App\Dungeon\Events\DwellerDespawned;
use App\Dungeon\Point;
use App\Dungeon\Tile;
use PHPUnit\Framework\Attributes\Test;

final class KillDwellerMinorTest extends DungeonTest
{
    #[Test]
    public function interact_with_tile_despawns_dweller_and_unsets_active_card(): void
    {
        $card = new KillDwellerMinor();
        $this->dungeon->setActiveCard($card);
        $point = new Point(2, 0);
        $tile = new Tile($point);
        $this->dungeon->addTile($tile);
        $this->dungeon->spawnDweller($point);

        $card->interactWithTile($this->dungeon, $tile);

        $this->assertNull($this->dungeon->getDweller($point));
        $this->assertNull($this->dungeon->activeCard);
        $this->eventBus->assertDispatched(DwellerDespawned::class);
        $this->eventBus->assertDispatched(ActiveCardUnset::class);
        $this->eventBus->assertDispatched(CardUpdated::class);
    }

    #[Test]
    public function can_interact_with_tile_returns_true_when_dweller_is_present(): void
    {
        $card = new KillDwellerMinor();
        $point = new Point(2, 0);
        $tile = new Tile($point);
        $this->dungeon->addTile($tile);
        $this->dungeon->spawnDweller($point);

        $this->assertTrue($card->canInteractWithTile($this->dungeon, $tile));
    }

    #[Test]
    public function can_interact_with_tile_returns_false_when_no_dweller_present(): void
    {
        $card = new KillDwellerMinor();
        $tile = new Tile(new Point(2, 0));

        $this->assertFalse($card->canInteractWithTile($this->dungeon, $tile));
    }
}
