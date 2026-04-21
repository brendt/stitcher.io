<?php

namespace Tests\Dungeon\Cards;

use Tests\Dungeon\DungeonTest;

use App\Dungeon\Cards\KillDwellerMajor;
use App\Dungeon\Events\ActiveCardUnset;
use App\Dungeon\Events\CardUpdated;
use App\Dungeon\Events\DwellerDespawned;
use App\Dungeon\Point;
use App\Dungeon\Tile;
use PHPUnit\Framework\Attributes\Test;

final class KillDwellerMajorTest extends DungeonTest
{
    #[Test]
    public function interact_with_tile_despawns_dweller_at_tile(): void
    {
        $card = new KillDwellerMajor();
        $point = new Point(2, 0);
        $tile = new Tile($point);
        $this->dungeon->addTile($tile);
        $this->dungeon->spawnDweller($point);

        $card->interactWithTile($this->dungeon, $tile);

        $this->assertNull($this->dungeon->getDweller($point));
        $this->eventBus->assertDispatched(DwellerDespawned::class);
        $this->eventBus->assertDispatched(CardUpdated::class);
    }

    #[Test]
    public function interact_with_tile_unsets_active_card_after_three_uses(): void
    {
        $card = new KillDwellerMajor();
        $this->dungeon->setActiveCard($card);

        for ($i = 0; $i < 3; $i++) {
            $point = new Point($i + 1, 0);
            $tile = new Tile($point);
            $this->dungeon->addTile($tile);
            $this->dungeon->spawnDweller($point);
            $card->interactWithTile($this->dungeon, $tile);
        }

        $this->assertNull($this->dungeon->activeCard);
        $this->eventBus->assertDispatched(ActiveCardUnset::class);
    }

    #[Test]
    public function interact_with_tile_does_not_unset_card_before_three_uses(): void
    {
        $card = new KillDwellerMajor();
        $this->dungeon->setActiveCard($card);
        $point = new Point(2, 0);
        $tile = new Tile($point);
        $this->dungeon->addTile($tile);
        $this->dungeon->spawnDweller($point);

        $card->interactWithTile($this->dungeon, $tile);

        $this->assertNotNull($this->dungeon->activeCard);
        $this->eventBus->assertNotDispatched(ActiveCardUnset::class);
    }

    #[Test]
    public function can_interact_with_tile_returns_true_when_dweller_is_present(): void
    {
        $card = new KillDwellerMajor();
        $point = new Point(2, 0);
        $tile = new Tile($point);
        $this->dungeon->addTile($tile);
        $this->dungeon->spawnDweller($point);

        $this->assertTrue($card->canInteractWithTile($this->dungeon, $tile));
    }

    #[Test]
    public function can_interact_with_tile_returns_false_when_no_dweller_present(): void
    {
        $card = new KillDwellerMajor();
        $tile = new Tile(new Point(2, 0));

        $this->assertFalse($card->canInteractWithTile($this->dungeon, $tile));
    }
}
