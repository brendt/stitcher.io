<?php

namespace Tests\Dungeon\Cards;

use App\Dungeon\Events\TileGenerated;
use App\Dungeon\Tile;
use Tests\Dungeon\DungeonTest;

use App\Dungeon\Cards\BeaconMajor;
use App\Dungeon\Events\CardUpdated;
use App\Dungeon\Events\DwellerUpdated;
use App\Dungeon\Events\PassiveCardUnset;
use App\Dungeon\Events\PlayerMoved;
use App\Dungeon\Events\CardPlayed;
use App\Dungeon\Point;
use PHPUnit\Framework\Attributes\Test;

final class BeaconMajorTest extends DungeonTest
{
    // -------------------------------------------------------------------------
    // play()
    // -------------------------------------------------------------------------

    #[Test]
    public function play_shows_all_hidden_dwellers(): void
    {
        $card = new BeaconMajor();
        $point = new Point(3, 0);
        $this->dungeon->spawnDweller($point);
        $dweller = $this->dungeon->getDweller($point);
        $dweller->isVisible = false;

        $card->play($this->dungeon);

        $this->assertTrue($dweller->isVisible);
        $this->eventBus->assertDispatched(DwellerUpdated::class);
    }

    #[Test]
    public function play_does_nothing_when_no_dwellers_exist(): void
    {
        $card = new BeaconMajor();
        $this->dungeon->dwellers = []; // clear initial dungeon dwellers

        $card->play($this->dungeon);

        $this->eventBus->assertNotDispatched(DwellerUpdated::class);
    }

    // -------------------------------------------------------------------------
    // handle() — PlayerMoved
    // -------------------------------------------------------------------------

    #[Test]
    public function handle_decrements_count_on_player_moved(): void
    {
        $card = new BeaconMajor();

        $card->handle($this->dungeon, new PlayerMoved(from: new Point(0, 0), to: new Point(1, 0)));

        $this->assertSame(24, $card->count);
    }

    #[Test]
    public function handle_updates_card_on_player_moved(): void
    {
        $card = new BeaconMajor();
        $this->dungeon->setPassiveCard($card);

        $card->handle($this->dungeon, new PlayerMoved(from: new Point(0, 0), to: new Point(1, 0)));

        $this->eventBus->assertDispatched(CardUpdated::class);
    }

    #[Test]
    public function handle_shows_dwellers_while_count_is_above_zero(): void
    {
        $card = new BeaconMajor();
        $point = new Point(3, 0);
        $this->dungeon->spawnDweller($point);
        $dweller = $this->dungeon->getDweller($point);
        $dweller->isVisible = false;

        $card->handle($this->dungeon, new PlayerMoved(from: new Point(0, 0), to: new Point(1, 0)));

        $this->assertTrue($dweller->isVisible);
        $this->assertSame(24, $card->count);
    }

    #[Test]
    public function handle_hides_dweller_outside_visibility_radius_when_count_reaches_zero(): void
    {
        $card = new BeaconMajor();
        $card->count = 1;
        $this->dungeon->setPassiveCard($card);

        // Dweller far outside visibility radius (default radius is 5)
        $farPoint = new Point(20, 0);
        $this->dungeon->spawnDweller($farPoint);
        $dweller = $this->dungeon->getDweller($farPoint);
        $dweller->isVisible = true;

        $card->handle($this->dungeon, new PlayerMoved(from: new Point(0, 0), to: new Point(1, 0)));

        $this->assertSame(0, $card->count);
        $this->assertFalse($dweller->isVisible);
        $this->assertNull($this->dungeon->passiveCard);
    }

    #[Test]
    public function handle_does_not_hide_dweller_inside_visibility_radius_when_count_reaches_zero(): void
    {
        $card = new BeaconMajor();
        $card->count = 1;
        $this->dungeon->setPassiveCard($card);

        // Dweller within visibility radius (default radius is 5)
        $nearPoint = new Point(2, 0);
        $this->dungeon->spawnDweller($nearPoint);
        $dweller = $this->dungeon->getDweller($nearPoint);
        $dweller->isVisible = true;

        $card->handle($this->dungeon, new PlayerMoved(from: new Point(0, 0), to: new Point(1, 0)));

        $this->assertSame(0, $card->count);
        $this->assertTrue($dweller->isVisible);
        $this->assertNull($this->dungeon->passiveCard);
        $this->eventBus->assertDispatched(PassiveCardUnset::class);
    }

    // -------------------------------------------------------------------------
    // handle() — CardPlayed
    // -------------------------------------------------------------------------

    #[Test]
    public function handle_does_not_decrement_count_on_card_played(): void
    {
        $card = new BeaconMajor();
        $this->dungeon->setPassiveCard($card);

        $card->handle($this->dungeon, new CardPlayed($card));

        $this->assertSame(25, $card->count);
        $this->eventBus->assertDispatched(CardUpdated::class);
    }

    #[Test]
    public function handle_shows_dwellers_on_card_played(): void
    {
        $card = new BeaconMajor();
        $point = new Point(3, 0);
        $this->dungeon->spawnDweller($point);
        $dweller = $this->dungeon->getDweller($point);
        $dweller->isVisible = false;

        $card->handle($this->dungeon, new CardPlayed($card));

        $this->assertTrue($dweller->isVisible);
    }

    // -------------------------------------------------------------------------
    // handle() — other events
    // -------------------------------------------------------------------------

    #[Test]
    public function handle_ignores_unrelated_events(): void
    {
        $card = new BeaconMajor();

        $card->handle($this->dungeon, new TileGenerated(
            new Tile(new Point(1, 0))
        ));

        $this->assertSame(25, $card->count);
        $this->eventBus->assertNotDispatched(CardUpdated::class);
    }
}
