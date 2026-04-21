<?php

namespace Tests\Dungeon;

use App\Dungeon\Cards\BeaconMinor;
use App\Dungeon\Direction;
use App\Dungeon\Events\ActiveCardSet;
use App\Dungeon\Events\ActiveCardUnset;
use App\Dungeon\Events\ArtifactCollected;
use App\Dungeon\Events\ArtifactSpawned;
use App\Dungeon\Events\CardDrawn;
use App\Dungeon\Events\CardPlayed;
use App\Dungeon\Events\CardUpdated;
use App\Dungeon\Events\DwellerDespawned;
use App\Dungeon\Events\DwellerMoved;
use App\Dungeon\Events\DwellerSpawned;
use App\Dungeon\Events\DwellerUpdated;
use App\Dungeon\Events\PassiveCardSet;
use App\Dungeon\Events\PassiveCardUnset;
use App\Dungeon\Events\PermanentCardAdded;
use App\Dungeon\Events\PlayerCoinsIncreased;
use App\Dungeon\Events\PlayerExited;
use App\Dungeon\Events\PlayerHealthDecreased;
use App\Dungeon\Events\PlayerHealthIncreased;
use App\Dungeon\Events\PlayerManaDecreased;
use App\Dungeon\Events\PlayerManaIncreased;
use App\Dungeon\Events\PlayerMaxHealthIncreased;
use App\Dungeon\Events\PlayerMaxManaIncreased;
use App\Dungeon\Events\PlayerMoved;
use App\Dungeon\Events\PlayerResigned;
use App\Dungeon\Events\PlayerShardsIncreased;
use App\Dungeon\Events\PlayerStabilityDecreased;
use App\Dungeon\Events\PlayerStabilityIncreased;
use App\Dungeon\Events\PlayerVictoryPointsIncreased;
use App\Dungeon\Events\TileCoinsAdded;
use App\Dungeon\Events\TileCoinsCollected;
use App\Dungeon\Events\TileCollapsed;
use App\Dungeon\Events\TileGenerated;
use App\Dungeon\Events\TileUpdated;
use App\Dungeon\Events\VisibilityChanged;
use App\Dungeon\Point;
use App\Dungeon\Tile;
use PHPUnit\Framework\Attributes\Test;

final class DungeonActionsTest extends DungeonTest
{
    // -------------------------------------------------------------------------
    // generateTile
    // -------------------------------------------------------------------------

    #[Test]
    public function generate_tile_adds_new_tile(): void
    {
        $point = new Point(x: 1, y: 1);

        $this->dungeon->generateTile(from: null, to: $point);

        $this->assertNotNull($this->dungeon->tryTile($point));

        $this->eventBus->assertDispatched(TileGenerated::class, function (TileGenerated $event) use ($point) {
            $this->assertTrue($event->tile->point->equals($point));
        });
    }

    #[Test]
    public function generate_tile_does_not_overwrite_existing_tile(): void
    {
        $point = new Point(0, 0); // origin tile already exists

        $tileCountBefore = $this->dungeon->tileCount();

        $this->dungeon->generateTile(from: null, to: $point);

        $this->assertSame($tileCountBefore, $this->dungeon->tileCount());
        $this->eventBus->assertNotDispatched(TileGenerated::class);
    }

    // -------------------------------------------------------------------------
    // move
    // -------------------------------------------------------------------------

    #[Test]
    public function move_updates_player_position(): void
    {
        $this->dungeon->move(Direction::RIGHT);

        $this->assertTrue($this->dungeon->playerPosition->equals(new Point(1, 0)));

        $this->eventBus->assertDispatched(PlayerMoved::class, function (PlayerMoved $event) {
            $this->assertTrue($event->from->equals(new Point(0, 0)));
            $this->assertTrue($event->to->equals(new Point(1, 0)));
        });
    }

    #[Test]
    public function move_generates_new_tile_when_no_tile_exists_at_destination(): void
    {
        $this->assertNull($this->dungeon->tryTile(new Point(1, 0)));

        $this->dungeon->move(Direction::RIGHT);

        $this->assertNotNull($this->dungeon->tryTile(new Point(1, 0)));
        $this->eventBus->assertDispatched(TileGenerated::class);
    }

    #[Test]
    public function move_does_not_move_when_current_tile_has_no_opening_in_that_direction(): void
    {
        $this->dungeon->addTile(new Tile(new Point(0, 0), directions: [Direction::TOP, Direction::BOTTOM], isOrigin: true));

        $this->dungeon->move(Direction::RIGHT);

        $this->assertTrue($this->dungeon->playerPosition->equals(new Point(0, 0)));
        $this->eventBus->assertNotDispatched(PlayerMoved::class);
    }

    #[Test]
    public function move_does_not_move_into_collapsed_tile(): void
    {
        $this->dungeon->addTile(new Tile(new Point(1, 0), isCollapsed: true));

        $this->dungeon->move(Direction::RIGHT);

        $this->assertTrue($this->dungeon->playerPosition->equals(new Point(0, 0)));
        $this->eventBus->assertNotDispatched(PlayerMoved::class);
    }

    #[Test]
    public function move_does_not_move_when_neighbour_tile_has_no_opening_on_its_entry_side(): void
    {
        $this->dungeon->addTile(new Tile(new Point(1, 0), directions: [Direction::RIGHT, Direction::TOP, Direction::BOTTOM]));

        $this->dungeon->move(Direction::RIGHT);

        $this->assertTrue($this->dungeon->playerPosition->equals(new Point(0, 0)));
        $this->eventBus->assertNotDispatched(PlayerMoved::class);
    }

    // -------------------------------------------------------------------------
    // addCoinsToTile
    // -------------------------------------------------------------------------

    #[Test]
    public function add_coins_to_tile(): void
    {
        $tile = $this->dungeon->currentTile;

        $this->dungeon->addCoinsToTile($tile, 50);

        $this->assertSame(50, $tile->coins);

        $this->eventBus->assertDispatched(TileCoinsAdded::class, function (TileCoinsAdded $event) use ($tile) {
            $this->assertTrue($event->tile->point->equals($tile->point));
            $this->assertSame(50, $event->amount);
        });
    }

    // -------------------------------------------------------------------------
    // collectCoins
    // -------------------------------------------------------------------------

    #[Test]
    public function collect_coins(): void
    {
        $tile = $this->dungeon->currentTile;
        $tile->coins = 75;

        $this->dungeon->collectCoins($tile);

        $this->assertSame(75, $this->dungeon->coins);
        $this->assertSame(0, $tile->coins);

        $this->eventBus->assertDispatched(TileCoinsCollected::class, function (TileCoinsCollected $event) {
            $this->assertSame(75, $event->amount);
            $this->assertSame(75, $event->total);
        });
    }

    // -------------------------------------------------------------------------
    // increaseMaxMana
    // -------------------------------------------------------------------------

    #[Test]
    public function increase_max_mana(): void
    {
        $this->dungeon->increaseMaxMana(50);

        $this->assertSame(200, $this->dungeon->maxMana);

        $this->eventBus->assertDispatched(PlayerMaxManaIncreased::class, function (PlayerMaxManaIncreased $event) {
            $this->assertSame(50, $event->amount);
            $this->assertSame(200, $event->total);
        });
    }

    // -------------------------------------------------------------------------
    // increaseMana / decreaseMana
    // -------------------------------------------------------------------------

    #[Test]
    public function increase_mana(): void
    {
        $this->dungeon->increaseMana(30);

        $this->assertSame(30, $this->dungeon->mana);

        $this->eventBus->assertDispatched(PlayerManaIncreased::class, function (PlayerManaIncreased $event) {
            $this->assertSame(30, $event->amount);
            $this->assertSame(30, $event->total);
        });
    }

    #[Test]
    public function increase_mana_is_capped_at_max_mana(): void
    {
        $this->dungeon->increaseMana(200); // maxMana is 150

        $this->assertSame(150, $this->dungeon->mana);

        $this->eventBus->assertDispatched(PlayerManaIncreased::class, function (PlayerManaIncreased $event) {
            $this->assertSame(150, $event->amount);
        });
    }

    #[Test]
    public function increase_mana_does_nothing_when_already_at_max(): void
    {
        $this->dungeon->mana = 150;

        $this->dungeon->increaseMana(10);

        $this->assertSame(150, $this->dungeon->mana);
        $this->eventBus->assertNotDispatched(PlayerManaIncreased::class);
    }

    #[Test]
    public function decrease_mana(): void
    {
        $this->dungeon->mana = 100;

        $this->dungeon->decreaseMana(30);

        $this->assertSame(70, $this->dungeon->mana);

        $this->eventBus->assertDispatched(PlayerManaDecreased::class, function (PlayerManaDecreased $event) {
            $this->assertSame(30, $event->amount);
            $this->assertSame(70, $event->total);
        });
    }

    #[Test]
    public function decrease_mana_is_clamped_at_zero(): void
    {
        $this->dungeon->mana = 10;

        $this->dungeon->decreaseMana(50);

        $this->assertSame(0, $this->dungeon->mana);

        $this->eventBus->assertDispatched(PlayerManaDecreased::class, function (PlayerManaDecreased $event) {
            $this->assertSame(10, $event->amount);
            $this->assertSame(0, $event->total);
        });
    }

    // -------------------------------------------------------------------------
    // increaseCoins
    // -------------------------------------------------------------------------

    #[Test]
    public function increase_coins(): void
    {
        $this->dungeon->increaseCoins(100);

        $this->assertSame(100, $this->dungeon->coins);

        $this->eventBus->assertDispatched(PlayerCoinsIncreased::class, function (PlayerCoinsIncreased $event) {
            $this->assertSame(100, $event->amount);
            $this->assertSame(100, $event->total);
        });
    }

    // -------------------------------------------------------------------------
    // increaseMaxHealth
    // -------------------------------------------------------------------------

    #[Test]
    public function increase_max_health(): void
    {
        $this->dungeon->increaseMaxHealth(50);

        $this->assertSame(150, $this->dungeon->maxHealth);

        $this->eventBus->assertDispatched(PlayerMaxHealthIncreased::class, function (PlayerMaxHealthIncreased $event) {
            $this->assertSame(50, $event->amount);
            $this->assertSame(150, $event->total);
        });
    }

    // -------------------------------------------------------------------------
    // increaseHealth / decreaseHealth
    // -------------------------------------------------------------------------

    #[Test]
    public function increase_health(): void
    {
        $this->dungeon->health = 50;

        $this->dungeon->increaseHealth(30);

        $this->assertSame(80, $this->dungeon->health);

        $this->eventBus->assertDispatched(PlayerHealthIncreased::class, function (PlayerHealthIncreased $event) {
            $this->assertSame(30, $event->amount);
            $this->assertSame(80, $event->total);
        });
    }

    #[Test]
    public function increase_health_is_capped_at_max_health(): void
    {
        $this->dungeon->health = 80;

        $this->dungeon->increaseHealth(50); // maxHealth is 100

        $this->assertSame(100, $this->dungeon->health);

        $this->eventBus->assertDispatched(PlayerHealthIncreased::class, function (PlayerHealthIncreased $event) {
            $this->assertSame(20, $event->amount);
        });
    }

    #[Test]
    public function increase_health_does_nothing_when_already_at_max(): void
    {
        $this->dungeon->increaseHealth(10); // already at 100

        $this->assertSame(100, $this->dungeon->health);
        $this->eventBus->assertNotDispatched(PlayerHealthIncreased::class);
    }

    #[Test]
    public function decrease_health(): void
    {
        $this->dungeon->decreaseHealth(30);

        $this->assertSame(70, $this->dungeon->health);

        $this->eventBus->assertDispatched(PlayerHealthDecreased::class, function (PlayerHealthDecreased $event) {
            $this->assertSame(30, $event->amount);
            $this->assertSame(70, $event->total);
        });
    }

    #[Test]
    public function decrease_health_is_clamped_at_zero(): void
    {
        $this->dungeon->health = 10;

        $this->dungeon->decreaseHealth(50);

        $this->assertSame(0, $this->dungeon->health);

        $this->eventBus->assertDispatched(PlayerHealthDecreased::class, function (PlayerHealthDecreased $event) {
            $this->assertSame(10, $event->amount);
            $this->assertSame(0, $event->total);
        });
    }

    #[Test]
    public function decrease_health_includes_reason_in_event(): void
    {
        $this->dungeon->decreaseHealth(10, 'Trap triggered');

        $this->eventBus->assertDispatched(PlayerHealthDecreased::class, function (PlayerHealthDecreased $event) {
            $this->assertSame('Trap triggered', $event->reason);
        });
    }

    // -------------------------------------------------------------------------
    // decreaseStability / increaseStability
    // -------------------------------------------------------------------------

    #[Test]
    public function decrease_stability(): void
    {
        $this->dungeon->decreaseStability(20);

        $this->assertSame(80, $this->dungeon->stability);

        $this->eventBus->assertDispatched(PlayerStabilityDecreased::class, function (PlayerStabilityDecreased $event) {
            $this->assertSame(20, $event->amount);
            $this->assertSame(80, $event->total);
        });
    }

    #[Test]
    public function decrease_stability_is_clamped_at_zero(): void
    {
        $this->dungeon->stability = 10;

        $this->dungeon->decreaseStability(50);

        $this->assertSame(0, $this->dungeon->stability);

        $this->eventBus->assertDispatched(PlayerStabilityDecreased::class, function (PlayerStabilityDecreased $event) {
            $this->assertSame(10, $event->amount);
            $this->assertSame(0, $event->total);
        });
    }

    #[Test]
    public function increase_stability(): void
    {
        $this->dungeon->stability = 50;

        $this->dungeon->increaseStability(30);

        $this->assertSame(80, $this->dungeon->stability);

        $this->eventBus->assertDispatched(PlayerStabilityIncreased::class, function (PlayerStabilityIncreased $event) {
            $this->assertSame(30, $event->amount);
            $this->assertSame(80, $event->total);
        });
    }

    #[Test]
    public function increase_stability_is_capped_at_max_stability(): void
    {
        $this->dungeon->stability = 80;

        $this->dungeon->increaseStability(50); // maxStability is 100

        $this->assertSame(100, $this->dungeon->stability);

        $this->eventBus->assertDispatched(PlayerStabilityIncreased::class, function (PlayerStabilityIncreased $event) {
            $this->assertSame(20, $event->amount);
        });
    }

    #[Test]
    public function increase_stability_does_nothing_when_already_at_max(): void
    {
        $this->dungeon->increaseStability(10); // already at 100

        $this->assertSame(100, $this->dungeon->stability);
        $this->eventBus->assertNotDispatched(PlayerStabilityIncreased::class);
    }

    // -------------------------------------------------------------------------
    // collapseTile
    // -------------------------------------------------------------------------

    #[Test]
    public function collapse_tile(): void
    {
        $tile = new Tile(new Point(5, 5));
        $this->dungeon->addTile($tile);

        $this->dungeon->collapseTile($tile);

        $this->assertTrue($tile->isCollapsed);

        $this->eventBus->assertDispatched(TileCollapsed::class, function (TileCollapsed $event) use ($tile) {
            $this->assertTrue($event->tile->point->equals($tile->point));
        });
    }

    #[Test]
    public function collapse_tile_does_not_collapse_origin_tile(): void
    {
        $originTile = $this->dungeon->currentTile;

        $this->dungeon->collapseTile($originTile);

        $this->assertFalse($originTile->isCollapsed);
        $this->eventBus->assertNotDispatched(TileCollapsed::class);
    }

    #[Test]
    public function collapse_tile_does_not_collapse_already_collapsed_tile(): void
    {
        $tile = new Tile(new Point(5, 5), isCollapsed: true);
        $this->dungeon->addTile($tile);

        $this->dungeon->collapseTile($tile);

        $this->eventBus->assertNotDispatched(TileCollapsed::class);
    }

    // -------------------------------------------------------------------------
    // playCard
    // -------------------------------------------------------------------------

    #[Test]
    public function play_card(): void
    {
        $card = new BeaconMinor();
        $this->dungeon->hand[$card->id] = $card;
        $this->dungeon->mana = 100;

        $this->dungeon->playCard($card->id);

        $this->assertArrayNotHasKey($card->id, $this->dungeon->hand);

        $this->eventBus->assertDispatched(CardPlayed::class, function (CardPlayed $event) use ($card) {
            $this->assertSame($card->id, $event->card->id);
        });
    }

    #[Test]
    public function play_card_deducts_mana(): void
    {
        $card = new BeaconMinor(); // costs 75 mana
        $this->dungeon->hand[$card->id] = $card;
        $this->dungeon->mana = 100;

        $this->dungeon->playCard($card->id);

        $this->assertSame(25, $this->dungeon->mana);
    }

    #[Test]
    public function play_card_does_nothing_when_card_not_in_hand(): void
    {
        $this->dungeon->playCard('non-existent-card-id');

        $this->eventBus->assertNotDispatched(CardPlayed::class);
    }

    #[Test]
    public function play_card_does_nothing_when_not_enough_mana(): void
    {
        $card = new BeaconMinor(); // costs 75 mana
        $this->dungeon->hand[$card->id] = $card;
        $this->dungeon->mana = 10;

        $this->dungeon->playCard($card->id);

        $this->assertArrayHasKey($card->id, $this->dungeon->hand);
        $this->eventBus->assertNotDispatched(CardPlayed::class);
    }

    #[Test]
    public function play_card_does_nothing_when_passive_slot_is_already_occupied(): void
    {
        $card = new BeaconMinor(); // Type::PASSIVE
        $this->dungeon->hand[$card->id] = $card;
        $this->dungeon->mana = 150;
        $this->dungeon->passiveCard = new BeaconMinor();

        $this->dungeon->playCard($card->id);

        $this->assertArrayHasKey($card->id, $this->dungeon->hand);
        $this->eventBus->assertNotDispatched(CardPlayed::class);
    }

    #[Test]
    public function play_card_does_nothing_when_active_slot_is_already_occupied(): void
    {
        // TODO: requires a concrete active card implementation (Type::ACTIVE)
        $this->markTestSkipped('Requires a concrete active card implementation');
    }

    // -------------------------------------------------------------------------
    // drawCard
    // -------------------------------------------------------------------------

    #[Test]
    public function draw_card(): void
    {
        $card = new BeaconMinor();
        $this->dungeon->deck[$card->id] = $card;
        $this->dungeon->hand = [];

        $this->dungeon->drawCard();

        $this->assertArrayHasKey($card->id, $this->dungeon->hand);
        $this->assertArrayNotHasKey($card->id, $this->dungeon->deck);

        $this->eventBus->assertDispatched(CardDrawn::class);
    }

    #[Test]
    public function draw_card_does_nothing_when_hand_is_full(): void
    {
        for ($i = 0; $i < $this->dungeon->maxHandCount; $i++) {
            $card = new BeaconMinor();
            $this->dungeon->hand[$card->id] = $card;
        }

        $extra = new BeaconMinor();
        $this->dungeon->deck[$extra->id] = $extra;

        $this->dungeon->drawCard();

        $this->assertArrayNotHasKey($extra->id, $this->dungeon->hand);
        $this->eventBus->assertNotDispatched(CardDrawn::class);
    }

    #[Test]
    public function draw_card_does_nothing_when_deck_is_empty(): void
    {
        $this->dungeon->deck = [];
        $this->dungeon->hand = [];

        $this->dungeon->drawCard();

        $this->assertEmpty($this->dungeon->hand);
        $this->eventBus->assertNotDispatched(CardDrawn::class);
    }

    // -------------------------------------------------------------------------
    // setPassiveCard / unsetPassiveCard
    // -------------------------------------------------------------------------

    #[Test]
    public function set_passive_card(): void
    {
        $card = new BeaconMinor();

        $this->dungeon->setPassiveCard($card);

        $this->assertSame($card, $this->dungeon->passiveCard);

        $this->eventBus->assertDispatched(PassiveCardSet::class, function (PassiveCardSet $event) use ($card) {
            $this->assertSame($card->id, $event->card->id);
        });
    }

    #[Test]
    public function unset_passive_card(): void
    {
        $this->dungeon->passiveCard = new BeaconMinor();

        $this->dungeon->unsetPassiveCard();

        $this->assertNull($this->dungeon->passiveCard);
        $this->eventBus->assertDispatched(PassiveCardUnset::class);
    }

    // -------------------------------------------------------------------------
    // setActiveCard / unsetActiveCard
    // -------------------------------------------------------------------------

    #[Test]
    public function set_active_card(): void
    {
        $card = new BeaconMinor();

        $this->dungeon->setActiveCard($card);

        $this->assertSame($card, $this->dungeon->activeCard);

        $this->eventBus->assertDispatched(ActiveCardSet::class, function (ActiveCardSet $event) use ($card) {
            $this->assertSame($card->id, $event->card->id);
        });
    }

    #[Test]
    public function unset_active_card(): void
    {
        $this->dungeon->activeCard = new BeaconMinor();

        $this->dungeon->unsetActiveCard();

        $this->assertNull($this->dungeon->activeCard);
        $this->eventBus->assertDispatched(ActiveCardUnset::class);
    }

    // -------------------------------------------------------------------------
    // addPermanentCard
    // -------------------------------------------------------------------------

    #[Test]
    public function add_permanent_card(): void
    {
        $card = new BeaconMinor();

        $this->dungeon->addPermanentCard($card);

        $this->assertArrayHasKey($card->id, $this->dungeon->permanentCards);

        $this->eventBus->assertDispatched(PermanentCardAdded::class, function (PermanentCardAdded $event) use ($card) {
            $this->assertSame($card->id, $event->card->id);
        });
    }

    // -------------------------------------------------------------------------
    // notifyCards
    // -------------------------------------------------------------------------

    #[Test]
    public function notify_cards(): void
    {
        // TODO: requires a PassiveCard implementation and a DungeonEvent; verify handle() is called on active/passive/permanent cards
        $this->markTestSkipped('Requires a PassiveCard implementation and a DungeonEvent');
    }

    // -------------------------------------------------------------------------
    // interactWithTile
    // -------------------------------------------------------------------------

    #[Test]
    public function interact_with_tile(): void
    {
        // TODO: requires a concrete ActiveCard implementation
        $this->markTestSkipped('Requires a concrete active card implementation');
    }

    // -------------------------------------------------------------------------
    // removeTileCollapse
    // -------------------------------------------------------------------------

    #[Test]
    public function remove_tile_collapse(): void
    {
        $tile = new Tile(new Point(5, 5), isCollapsed: true);
        $this->dungeon->addTile($tile);

        $this->dungeon->removeTileCollapse($tile);

        $this->assertFalse($tile->isCollapsed);

        $this->eventBus->assertDispatched(TileUpdated::class, function (TileUpdated $event) use ($tile) {
            $this->assertTrue($event->tile->point->equals($tile->point));
        });
    }

    #[Test]
    public function remove_tile_collapse_does_nothing_when_tile_is_not_collapsed(): void
    {
        $tile = new Tile(new Point(5, 5));
        $this->dungeon->addTile($tile);

        $this->dungeon->removeTileCollapse($tile);

        $this->eventBus->assertNotDispatched(TileUpdated::class);
    }

    // -------------------------------------------------------------------------
    // removeTileWalls
    // -------------------------------------------------------------------------

    #[Test]
    public function remove_tile_walls_opens_all_directions(): void
    {
        $tile = new Tile(new Point(5, 5), directions: [Direction::TOP]);
        $this->dungeon->addTile($tile);

        $this->dungeon->removeTileWalls($tile);

        $this->assertEqualsCanonicalizing(Direction::cases(), $tile->directions);
        $this->eventBus->assertDispatched(TileUpdated::class);
    }

    // -------------------------------------------------------------------------
    // spawnDweller / despawnDweller / moveDweller
    // -------------------------------------------------------------------------

    #[Test]
    public function spawn_dweller(): void
    {
        $point = new Point(3, 3);

        $this->dungeon->spawnDweller($point);

        $this->assertNotNull($this->dungeon->getDweller($point));

        $this->eventBus->assertDispatched(DwellerSpawned::class, function (DwellerSpawned $event) use ($point) {
            $this->assertTrue($event->dweller->point->equals($point));
        });
    }

    #[Test]
    public function despawn_dweller(): void
    {
        $point = new Point(3, 3);
        $this->dungeon->spawnDweller($point);
        $dweller = $this->dungeon->getDweller($point);

        $this->dungeon->despawnDweller($dweller);

        $this->assertNull($this->dungeon->getDweller($point));

        $this->eventBus->assertDispatched(DwellerDespawned::class, function (DwellerDespawned $event) use ($point) {
            $this->assertTrue($event->dweller->point->equals($point));
        });
    }

    #[Test]
    public function move_dweller(): void
    {
        $from = new Point(3, 3);
        $to = new Point(4, 3);
        $this->dungeon->spawnDweller($from);
        $dweller = $this->dungeon->getDweller($from);

        $this->dungeon->moveDweller($dweller, $to);

        $this->assertNull($this->dungeon->getDweller($from));
        $this->assertNotNull($this->dungeon->getDweller($to));

        $this->eventBus->assertDispatched(DwellerMoved::class, function (DwellerMoved $event) use ($from, $to) {
            $this->assertTrue($event->from->equals($from));
            $this->assertTrue($event->to->equals($to));
        });
    }

    // -------------------------------------------------------------------------
    // showDweller / hideDweller
    // -------------------------------------------------------------------------

    #[Test]
    public function show_dweller(): void
    {
        $point = new Point(3, 3);
        $this->dungeon->spawnDweller($point);
        $dweller = $this->dungeon->getDweller($point);
        $dweller->isVisible = false;

        $this->dungeon->showDweller($dweller);

        $this->assertTrue($dweller->isVisible);
        $this->eventBus->assertDispatched(DwellerUpdated::class);
    }

    #[Test]
    public function show_dweller_does_nothing_when_already_visible(): void
    {
        $point = new Point(3, 3);
        $this->dungeon->spawnDweller($point);
        $dweller = $this->dungeon->getDweller($point);
        $dweller->isVisible = true;

        $this->dungeon->showDweller($dweller);

        $this->eventBus->assertNotDispatched(DwellerUpdated::class);
    }

    #[Test]
    public function hide_dweller(): void
    {
        $point = new Point(3, 3);
        $this->dungeon->spawnDweller($point);
        $dweller = $this->dungeon->getDweller($point);
        $dweller->isVisible = true;

        $this->dungeon->hideDweller($dweller);

        $this->assertFalse($dweller->isVisible);
        $this->eventBus->assertDispatched(DwellerUpdated::class);
    }

    #[Test]
    public function hide_dweller_does_nothing_when_already_hidden(): void
    {
        $point = new Point(3, 3);
        $this->dungeon->spawnDweller($point);
        $dweller = $this->dungeon->getDweller($point);
        $dweller->isVisible = false;

        $this->dungeon->hideDweller($dweller);

        $this->eventBus->assertNotDispatched(DwellerUpdated::class);
    }

    // -------------------------------------------------------------------------
    // changeVisibility
    // -------------------------------------------------------------------------

    #[Test]
    public function change_visibility(): void
    {
        $this->dungeon->changeVisibility(10);

        $this->assertSame(10, $this->dungeon->visibilityRadius);

        $this->eventBus->assertDispatched(VisibilityChanged::class, function (VisibilityChanged $event) {
            $this->assertSame(10, $event->visibilityRadius);
        });
    }

    // -------------------------------------------------------------------------
    // spawnArtifact
    // -------------------------------------------------------------------------

    #[Test]
    public function spawn_artifact(): void
    {
        $point = new Point(7, 7);

        $this->dungeon->spawnArtifact($point);

        $this->assertTrue($this->dungeon->artifactLocation->equals($point));

        $this->eventBus->assertDispatched(ArtifactSpawned::class, function (ArtifactSpawned $event) use ($point) {
            $this->assertTrue($event->point->equals($point));
        });
    }

    // -------------------------------------------------------------------------
    // spawnManaAltar / spawnHealthAltar / spawnStabilityAltar
    // (these register altar locations but do not dispatch events)
    // -------------------------------------------------------------------------

    #[Test]
    public function spawn_mana_altar(): void
    {
        $point = new Point(5, 5);

        $this->dungeon->spawnManaAltar($point);

        $this->assertSame($point, $this->dungeon->manaAltars[$point->x][$point->y]);
    }

    #[Test]
    public function spawn_health_altar(): void
    {
        $point = new Point(5, 5);

        $this->dungeon->spawnHealthAltar($point);

        $this->assertSame($point, $this->dungeon->healthAltars[$point->x][$point->y]);
    }

    #[Test]
    public function spawn_stability_altar(): void
    {
        $point = new Point(5, 5);

        $this->dungeon->spawnStabilityAltar($point);

        $this->assertSame($point, $this->dungeon->stabilityAltars[$point->x][$point->y]);
    }

    // -------------------------------------------------------------------------
    // spawnVictoryPoint / spawnShard
    // (these register locations but do not dispatch events)
    // -------------------------------------------------------------------------

    #[Test]
    public function spawn_victory_point(): void
    {
        $point = new Point(5, 5);

        $this->dungeon->spawnVictoryPoint($point);

        $this->assertSame($point, $this->dungeon->victoryPointLocations[$point->x][$point->y]);
    }

    #[Test]
    public function spawn_shard(): void
    {
        $point = new Point(5, 5);

        $this->dungeon->spawnShard($point);

        $this->assertSame($point, $this->dungeon->shardLocations[$point->x][$point->y]);
    }

    // -------------------------------------------------------------------------
    // increaseExperience (no event dispatched)
    // -------------------------------------------------------------------------

    #[Test]
    public function increase_experience(): void
    {
        $this->dungeon->increaseExperience(100);

        $this->assertSame(100, $this->dungeon->experience);
    }

    // -------------------------------------------------------------------------
    // collectArtifact
    // -------------------------------------------------------------------------

    #[Test]
    public function collect_artifact(): void
    {
        $this->dungeon->artifactLocation = clone $this->dungeon->playerPosition;
        $this->dungeon->coins = 0;
        $this->dungeon->mana = 0;

        $this->dungeon->collectArtifact();

        $this->assertGreaterThan(0, $this->dungeon->coins);
        $this->assertLessThan(100, $this->dungeon->stability);
        $this->assertGreaterThan(0, $this->dungeon->mana);

        $this->eventBus->assertDispatched(ArtifactCollected::class);
        $this->eventBus->assertDispatched(ArtifactSpawned::class); // a new artifact is spawned afterwards
    }

    #[Test]
    public function collect_artifact_does_nothing_when_player_is_not_on_artifact_location(): void
    {
        $this->dungeon->artifactLocation = new Point(99, 99);
        $this->dungeon->coins = 0;

        $this->dungeon->collectArtifact();

        $this->assertSame(0, $this->dungeon->coins);
        $this->eventBus->assertNotDispatched(ArtifactCollected::class);
    }

    // -------------------------------------------------------------------------
    // exit
    // -------------------------------------------------------------------------

    #[Test]
    public function exit_dungeon(): void
    {
        // Player starts on origin tile (0,0)

        $this->dungeon->exit();

        $this->assertTrue($this->dungeon->hasEnded);

        $this->eventBus->assertDispatched(PlayerExited::class, function (PlayerExited $event) {
            $this->assertSame($this->dungeon->user, $event->user);
        });
    }

    #[Test]
    public function exit_dungeon_does_nothing_when_not_on_origin_tile(): void
    {
        $this->dungeon->addTile(new Tile(new Point(1, 0)));
        $this->dungeon->playerPosition = new Point(1, 0);

        $this->dungeon->exit();

        $this->assertFalse($this->dungeon->hasEnded);
        $this->eventBus->assertNotDispatched(PlayerExited::class);
    }

    #[Test]
    public function exit_dungeon_without_origin_requirement(): void
    {
        $this->dungeon->playerPosition = new Point(5, 5);

        $this->dungeon->exit(requiresOrigin: false);

        $this->assertTrue($this->dungeon->hasEnded);
        $this->eventBus->assertDispatched(PlayerExited::class);
    }

    // -------------------------------------------------------------------------
    // resign
    // -------------------------------------------------------------------------

    #[Test]
    public function resign(): void
    {
        $this->dungeon->resign();

        $this->assertTrue($this->dungeon->hasEnded);

        $this->eventBus->assertDispatched(PlayerResigned::class, function (PlayerResigned $event) {
            $this->assertSame($this->dungeon->user, $event->user);
        });
    }

    // -------------------------------------------------------------------------
    // updateTile
    // -------------------------------------------------------------------------

    #[Test]
    public function update_tile(): void
    {
        $tile = $this->dungeon->currentTile;

        $this->dungeon->updateTile($tile);

        $this->eventBus->assertDispatched(TileUpdated::class, function (TileUpdated $event) use ($tile) {
            $this->assertTrue($event->tile->point->equals($tile->point));
        });
    }

    // -------------------------------------------------------------------------
    // increaseVictoryPoints
    // -------------------------------------------------------------------------

    #[Test]
    public function increase_victory_points(): void
    {
        $this->dungeon->increaseVictoryPoints(10);

        $this->assertSame(10, $this->dungeon->victoryPoints);

        $this->eventBus->assertDispatched(PlayerVictoryPointsIncreased::class, function (PlayerVictoryPointsIncreased $event) {
            $this->assertSame(10, $event->amount);
            $this->assertSame(10, $event->total);
        });
    }

    // -------------------------------------------------------------------------
    // increaseShards
    // -------------------------------------------------------------------------

    #[Test]
    public function increase_shards(): void
    {
        $this->dungeon->increaseShards(5);

        $this->assertSame(5, $this->dungeon->shards);

        $this->eventBus->assertDispatched(PlayerShardsIncreased::class, function (PlayerShardsIncreased $event) {
            $this->assertSame(5, $event->amount);
            $this->assertSame(5, $event->total);
        });
    }

    // -------------------------------------------------------------------------
    // updateCard
    // -------------------------------------------------------------------------

    #[Test]
    public function update_card(): void
    {
        $card = new BeaconMinor();

        $this->dungeon->updateCard($card);

        $this->eventBus->assertDispatched(CardUpdated::class, function (CardUpdated $event) use ($card) {
            $this->assertSame($card->id, $event->card->id);
        });
    }
}
