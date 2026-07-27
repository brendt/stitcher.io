<?php

declare(strict_types=1);

namespace Tests\Testo\Dungeon\Cards;

use App\Dungeon\Cards\KillDwellerMinor;
use App\Dungeon\Events\ActiveCardUnset;
use App\Dungeon\Events\CardUpdated;
use App\Dungeon\Events\DwellerDespawned;
use App\Dungeon\Point;
use App\Dungeon\Tile;
use Testo\Assert;
use Testo\Test;
use Tests\Testo\Dungeon\DungeonTest;

#[Test]
final class KillDwellerMinorTest extends DungeonTest
{
    public function interact_with_tile_despawns_dweller_and_unsets_active_card(): void
    {
        $card = new KillDwellerMinor();
        $this->dungeon->setActiveCard($card);
        $point = new Point(2, 0);
        $tile = new Tile($point);
        $this->dungeon->addTile($tile);
        $this->dungeon->spawnDweller($point);

        $card->interactWithTile($this->dungeon, $tile);

        Assert::null($this->dungeon->getDweller($point));
        Assert::null($this->dungeon->activeCard);
        $this->eventBus->assertDispatched(DwellerDespawned::class);
        $this->eventBus->assertDispatched(ActiveCardUnset::class);
        $this->eventBus->assertDispatched(CardUpdated::class);
    }

    public function can_interact_with_tile_returns_true_when_dweller_is_present(): void
    {
        $card = new KillDwellerMinor();
        $point = new Point(2, 0);
        $tile = new Tile($point);
        $this->dungeon->addTile($tile);
        $this->dungeon->spawnDweller($point);

        Assert::true($card->canInteractWithTile($this->dungeon, $tile));
    }

    public function can_interact_with_tile_returns_false_when_no_dweller_present(): void
    {
        $card = new KillDwellerMinor();
        $tile = new Tile(new Point(2, 0));

        Assert::false($card->canInteractWithTile($this->dungeon, $tile));
    }
}
