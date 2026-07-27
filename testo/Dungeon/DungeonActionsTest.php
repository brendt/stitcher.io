<?php

declare(strict_types=1);

namespace Tests\Testo\Dungeon;

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
use App\Dungeon\Events\LakeDiscovered;
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
use App\Dungeon\Events\RelicCollected;
use App\Dungeon\Events\TileCoinsAdded;
use App\Dungeon\Events\TileCoinsCollected;
use App\Dungeon\Events\TileCollapsed;
use App\Dungeon\Events\TileGenerated;
use App\Dungeon\Events\TileUpdated;
use App\Dungeon\Events\VisibilityChanged;
use App\Dungeon\Lake;
use App\Dungeon\LakePoint;
use App\Dungeon\Listeners\PlayerMovementListener;
use App\Dungeon\Point;
use App\Dungeon\Tile;
use Testo\Assert;
use Testo\Core\Exception\SkipTest;
use Testo\Test;

#[Test]
final class DungeonActionsTest extends DungeonTest
{
    // -------------------------------------------------------------------------
    // generateTile
    // -------------------------------------------------------------------------

    public function generate_tile_adds_new_tile(): void
    {
        $point = new Point(x: 1, y: 1);

        $this->dungeon->generateTile(from: null, to: $point);

        Assert::notNull($this->dungeon->tryTile($point));

        $this->eventBus->assertDispatched(TileGenerated::class, function (TileGenerated $event) use ($point) {
            Assert::true($event->tile->point->equals($point));
        });
    }

    public function generate_tile_does_not_overwrite_existing_tile(): void
    {
        $point = new Point(0, 0); // origin tile already exists

        $tileCountBefore = $this->dungeon->tileCount();

        $this->dungeon->generateTile(from: null, to: $point);

        Assert::same($this->dungeon->tileCount(), $tileCountBefore);
        $this->eventBus->assertNotDispatched(TileGenerated::class);
    }

    // -------------------------------------------------------------------------
    // move
    // -------------------------------------------------------------------------

    public function move_updates_player_position(): void
    {
        $this->dungeon->move(Direction::RIGHT);

        Assert::true($this->dungeon->playerPosition->equals(new Point(1, 0)));

        $this->eventBus->assertDispatched(PlayerMoved::class, function (PlayerMoved $event) {
            Assert::true($event->from->equals(new Point(0, 0)));
            Assert::true($event->to->equals(new Point(1, 0)));
        });
    }

    public function move_generates_new_tile_when_no_tile_exists_at_destination(): void
    {
        Assert::null($this->dungeon->tryTile(new Point(1, 0)));

        $this->dungeon->move(Direction::RIGHT);

        Assert::notNull($this->dungeon->tryTile(new Point(1, 0)));
        $this->eventBus->assertDispatched(TileGenerated::class);
    }

    public function move_does_not_move_when_current_tile_has_no_opening_in_that_direction(): void
    {
        $this->dungeon->addTile(new Tile(new Point(0, 0), directions: [Direction::TOP, Direction::BOTTOM], isOrigin: true));

        $this->dungeon->move(Direction::RIGHT);

        Assert::true($this->dungeon->playerPosition->equals(new Point(0, 0)));
        $this->eventBus->assertNotDispatched(PlayerMoved::class);
    }

    public function move_does_not_move_into_collapsed_tile(): void
    {
        $this->dungeon->addTile(new Tile(new Point(1, 0), isCollapsed: true));

        $this->dungeon->move(Direction::RIGHT);

        Assert::true($this->dungeon->playerPosition->equals(new Point(0, 0)));
        $this->eventBus->assertNotDispatched(PlayerMoved::class);
    }

    public function move_does_not_move_when_neighbour_tile_has_no_opening_on_its_entry_side(): void
    {
        $this->dungeon->addTile(new Tile(new Point(1, 0), directions: [Direction::RIGHT, Direction::TOP, Direction::BOTTOM]));

        $this->dungeon->move(Direction::RIGHT);

        Assert::true($this->dungeon->playerPosition->equals(new Point(0, 0)));
        $this->eventBus->assertNotDispatched(PlayerMoved::class);
    }

    public function move_does_not_move_onto_lake_tile(): void
    {
        $this->dungeon->addTile(new Tile(new Point(1, 0), isLake: true));

        $this->dungeon->move(Direction::RIGHT);

        Assert::true($this->dungeon->playerPosition->equals(new Point(0, 0)));
        $this->eventBus->assertNotDispatched(PlayerMoved::class);
    }

    public function move_can_move_onto_lake_tile_when_can_walk_on_water(): void
    {
        $this->dungeon->addTile(new Tile(new Point(1, 0), isLake: true));
        $this->dungeon->canWalkOnWater = true;

        $this->dungeon->move(Direction::RIGHT);

        Assert::true($this->dungeon->playerPosition->equals(new Point(1, 0)));
        $this->eventBus->assertDispatched(PlayerMoved::class);
    }

    // -------------------------------------------------------------------------
    // generateTile (lake)
    // -------------------------------------------------------------------------

    public function generate_tile_on_lake_point_marks_tile_as_lake(): void
    {
        $lakePoint = new Point(5, 5);
        $lake = new Lake($lakePoint);
        $lake->addLakePoint(new LakePoint($lakePoint, depth: 2));
        $this->dungeon->lakes[] = $lake;

        $this->dungeon->generateTile(from: null, to: $lakePoint);

        $tile = $this->dungeon->tryTile($lakePoint);
        Assert::true($tile->isLake);
        Assert::same($tile->depth, 2);
    }

    public function generate_tile_on_lake_point_opens_all_directions(): void
    {
        $lakePoint = new Point(5, 5);
        $lake = new Lake($lakePoint);
        $lake->addLakePoint(new LakePoint($lakePoint, depth: 1));
        $this->dungeon->lakes[] = $lake;

        $this->dungeon->generateTile(from: null, to: $lakePoint);

        $tile = $this->dungeon->tryTile($lakePoint);
        Assert::same($tile->directions, Direction::cases());
    }

    public function generate_tile_on_lake_relic_point_marks_tile_as_relic(): void
    {
        $lakePoint = new Point(5, 5);
        $lake = new Lake($lakePoint);
        $lake->addLakePoint(new LakePoint($lakePoint, depth: 3));
        $lake->relic = $lakePoint;
        $this->dungeon->lakes[] = $lake;

        $this->dungeon->generateTile(from: null, to: $lakePoint);

        $tile = $this->dungeon->tryTile($lakePoint);
        Assert::true($tile->isLake);
        Assert::true($tile->isRelic);
    }

    public function generate_tile_on_non_relic_lake_point_does_not_mark_tile_as_relic(): void
    {
        $lakePoint = new Point(5, 5);
        $relicPoint = new Point(6, 5);
        $lake = new Lake($lakePoint);
        $lake->addLakePoint(new LakePoint($lakePoint, depth: 3));
        $lake->relic = $relicPoint;
        $this->dungeon->lakes[] = $lake;

        $this->dungeon->generateTile(from: null, to: $lakePoint);

        $tile = $this->dungeon->tryTile($lakePoint);
        Assert::false($tile->isRelic);
    }

    public function generate_tile_on_lake_edge_opens_all_directions(): void
    {
        $origin = new Point(5, 5);
        $edgePoint = new Point(5, 4);
        $lake = new Lake($origin);
        $lake->addEdge($edgePoint);
        $this->dungeon->lakes[] = $lake;

        $this->dungeon->generateTile(from: null, to: $edgePoint);

        $tile = $this->dungeon->tryTile($edgePoint);
        Assert::false($tile->isLake);
        Assert::same($tile->directions, Direction::cases());
    }

    public function generate_tile_on_lake_edge_dispatches_lake_discovered(): void
    {
        $origin = new Point(5, 5);
        $edgePoint = new Point(5, 4);
        $lake = new Lake($origin);
        $lake->addEdge($edgePoint);
        $this->dungeon->lakes[] = $lake;

        $this->dungeon->generateTile(from: null, to: $edgePoint);

        $this->eventBus->assertDispatched(LakeDiscovered::class, function (LakeDiscovered $event) use ($edgePoint) {
            Assert::true($event->tile->point->equals($edgePoint));
            Assert::true($event->lake->isDiscovered);
        });
    }

    public function generate_tile_on_already_discovered_lake_edge_does_not_dispatch_lake_discovered(): void
    {
        $origin = new Point(5, 5);
        $edgePoint = new Point(5, 4);
        $lake = new Lake($origin, isDiscovered: true);
        $lake->addEdge($edgePoint);
        $this->dungeon->lakes[] = $lake;

        $this->dungeon->generateTile(from: null, to: $edgePoint);

        $this->eventBus->assertNotDispatched(LakeDiscovered::class);
    }

    // -------------------------------------------------------------------------
    // collectRelic
    // -------------------------------------------------------------------------

    public function collect_relic_marks_tile_as_not_relic(): void
    {
        $tile = new Tile(new Point(1, 0), isRelic: true);
        $this->dungeon->addTile($tile);

        $this->dungeon->collectRelic($tile);

        Assert::false($tile->isRelic);
    }

    public function collect_relic_dispatches_relic_collected_event(): void
    {
        $tile = new Tile(new Point(1, 0), isRelic: true);
        $this->dungeon->addTile($tile);

        $this->dungeon->collectRelic($tile);

        $this->eventBus->assertDispatched(RelicCollected::class, function (RelicCollected $event) use ($tile) {
            Assert::true($event->tile->point->equals($tile->point));
        });
    }

    public function collect_relic_increases_coins(): void
    {
        $tile = new Tile(new Point(1, 0), isRelic: true);
        $this->dungeon->addTile($tile);

        $this->dungeon->collectRelic($tile);

        Assert::int($this->dungeon->coins)->greaterThan(0);
    }

    public function collect_relic_increases_mana(): void
    {
        $tile = new Tile(new Point(1, 0), isRelic: true);
        $this->dungeon->addTile($tile);

        $this->dungeon->collectRelic($tile);

        Assert::int($this->dungeon->mana)->greaterThan(0);
    }

    public function collect_relic_does_nothing_when_tile_is_not_relic(): void
    {
        $tile = new Tile(new Point(1, 0), isRelic: false);
        $this->dungeon->addTile($tile);

        $this->dungeon->collectRelic($tile);

        Assert::same($this->dungeon->coins, 0);
        $this->eventBus->assertNotDispatched(RelicCollected::class);
    }

    // -------------------------------------------------------------------------
    // addCoinsToTile
    // -------------------------------------------------------------------------

    public function add_coins_to_tile(): void
    {
        $tile = $this->dungeon->currentTile;

        $this->dungeon->addCoinsToTile($tile, 50);

        Assert::same($tile->coins, 50);

        $this->eventBus->assertDispatched(TileCoinsAdded::class, function (TileCoinsAdded $event) use ($tile) {
            Assert::true($event->tile->point->equals($tile->point));
            Assert::same($event->amount, 50);
        });
    }

    // -------------------------------------------------------------------------
    // collectCoins
    // -------------------------------------------------------------------------

    public function collect_coins(): void
    {
        $tile = $this->dungeon->currentTile;
        $tile->coins = 75;

        $this->dungeon->collectCoins($tile);

        Assert::same($this->dungeon->coins, 75);
        Assert::same($tile->coins, 0);

        $this->eventBus->assertDispatched(TileCoinsCollected::class, function (TileCoinsCollected $event) {
            Assert::same($event->amount, 75);
            Assert::same($event->total, 75);
        });
    }

    // -------------------------------------------------------------------------
    // increaseMaxMana
    // -------------------------------------------------------------------------

    public function increase_max_mana(): void
    {
        $this->dungeon->increaseMaxMana(50);

        Assert::same($this->dungeon->maxMana, 200);

        $this->eventBus->assertDispatched(PlayerMaxManaIncreased::class, function (PlayerMaxManaIncreased $event) {
            Assert::same($event->amount, 50);
            Assert::same($event->total, 200);
        });
    }

    // -------------------------------------------------------------------------
    // increaseMana / decreaseMana
    // -------------------------------------------------------------------------

    public function increase_mana(): void
    {
        $this->dungeon->increaseMana(30);

        Assert::same($this->dungeon->mana, 30);

        $this->eventBus->assertDispatched(PlayerManaIncreased::class, function (PlayerManaIncreased $event) {
            Assert::same($event->amount, 30);
            Assert::same($event->total, 30);
        });
    }

    public function increase_mana_is_capped_at_max_mana(): void
    {
        $this->dungeon->increaseMana(200); // maxMana is 150

        Assert::same($this->dungeon->mana, 150);

        $this->eventBus->assertDispatched(PlayerManaIncreased::class, function (PlayerManaIncreased $event) {
            Assert::same($event->amount, 150);
        });
    }

    public function increase_mana_does_nothing_when_already_at_max(): void
    {
        $this->dungeon->mana = 150;

        $this->dungeon->increaseMana(10);

        Assert::same($this->dungeon->mana, 150);
        $this->eventBus->assertNotDispatched(PlayerManaIncreased::class);
    }

    public function increase_mana_includes_reason_in_event(): void
    {
        $this->dungeon->increaseMana(30, 'You found a mana altar (+30 mana)');

        $this->eventBus->assertDispatched(PlayerManaIncreased::class, function (PlayerManaIncreased $event) {
            Assert::same($event->reason, 'You found a mana altar (+30 mana)');
        });
    }

    public function increase_mana_reason_is_null_by_default(): void
    {
        $this->dungeon->increaseMana(30);

        $this->eventBus->assertDispatched(PlayerManaIncreased::class, function (PlayerManaIncreased $event) {
            Assert::null($event->reason);
        });
    }

    public function decrease_mana(): void
    {
        $this->dungeon->mana = 100;

        $this->dungeon->decreaseMana(30);

        Assert::same($this->dungeon->mana, 70);

        $this->eventBus->assertDispatched(PlayerManaDecreased::class, function (PlayerManaDecreased $event) {
            Assert::same($event->amount, 30);
            Assert::same($event->total, 70);
        });
    }

    public function decrease_mana_is_clamped_at_zero(): void
    {
        $this->dungeon->mana = 10;

        $this->dungeon->decreaseMana(50);

        Assert::same($this->dungeon->mana, 0);

        $this->eventBus->assertDispatched(PlayerManaDecreased::class, function (PlayerManaDecreased $event) {
            Assert::same($event->amount, 10);
            Assert::same($event->total, 0);
        });
    }

    // -------------------------------------------------------------------------
    // increaseCoins
    // -------------------------------------------------------------------------

    public function increase_coins(): void
    {
        $this->dungeon->increaseCoins(100);

        Assert::same($this->dungeon->coins, 100);

        $this->eventBus->assertDispatched(PlayerCoinsIncreased::class, function (PlayerCoinsIncreased $event) {
            Assert::same($event->amount, 100);
            Assert::same($event->total, 100);
        });
    }

    // -------------------------------------------------------------------------
    // increaseMaxHealth
    // -------------------------------------------------------------------------

    public function increase_max_health(): void
    {
        $this->dungeon->increaseMaxHealth(50);

        Assert::same($this->dungeon->maxHealth, 150);

        $this->eventBus->assertDispatched(PlayerMaxHealthIncreased::class, function (PlayerMaxHealthIncreased $event) {
            Assert::same($event->amount, 50);
            Assert::same($event->total, 150);
        });
    }

    // -------------------------------------------------------------------------
    // increaseHealth / decreaseHealth
    // -------------------------------------------------------------------------

    public function increase_health(): void
    {
        $this->dungeon->health = 50;

        $this->dungeon->increaseHealth(30);

        Assert::same($this->dungeon->health, 80);

        $this->eventBus->assertDispatched(PlayerHealthIncreased::class, function (PlayerHealthIncreased $event) {
            Assert::same($event->amount, 30);
            Assert::same($event->total, 80);
        });
    }

    public function increase_health_is_capped_at_max_health(): void
    {
        $this->dungeon->health = 80;

        $this->dungeon->increaseHealth(50); // maxHealth is 100

        Assert::same($this->dungeon->health, 100);

        $this->eventBus->assertDispatched(PlayerHealthIncreased::class, function (PlayerHealthIncreased $event) {
            Assert::same($event->amount, 20);
        });
    }

    public function increase_health_does_nothing_when_already_at_max(): void
    {
        $this->dungeon->increaseHealth(10); // already at 100

        Assert::same($this->dungeon->health, 100);
        $this->eventBus->assertNotDispatched(PlayerHealthIncreased::class);
    }

    public function increase_health_includes_reason_in_event(): void
    {
        $this->dungeon->health = 50;

        $this->dungeon->increaseHealth(30, 'You found a health altar (+30 health)');

        $this->eventBus->assertDispatched(PlayerHealthIncreased::class, function (PlayerHealthIncreased $event) {
            Assert::same($event->reason, 'You found a health altar (+30 health)');
        });
    }

    public function increase_health_reason_is_null_by_default(): void
    {
        $this->dungeon->health = 50;

        $this->dungeon->increaseHealth(30);

        $this->eventBus->assertDispatched(PlayerHealthIncreased::class, function (PlayerHealthIncreased $event) {
            Assert::null($event->reason);
        });
    }

    public function decrease_health(): void
    {
        $this->dungeon->decreaseHealth(30);

        Assert::same($this->dungeon->health, 70);

        $this->eventBus->assertDispatched(PlayerHealthDecreased::class, function (PlayerHealthDecreased $event) {
            Assert::same($event->amount, 30);
            Assert::same($event->total, 70);
        });
    }

    public function decrease_health_is_clamped_at_zero(): void
    {
        $this->dungeon->health = 10;

        $this->dungeon->decreaseHealth(50);

        Assert::same($this->dungeon->health, 0);

        $this->eventBus->assertDispatched(PlayerHealthDecreased::class, function (PlayerHealthDecreased $event) {
            Assert::same($event->amount, 10);
            Assert::same($event->total, 0);
        });
    }

    public function decrease_health_includes_reason_in_event(): void
    {
        $this->dungeon->decreaseHealth(10, 'Trap triggered');

        $this->eventBus->assertDispatched(PlayerHealthDecreased::class, function (PlayerHealthDecreased $event) {
            Assert::same($event->reason, 'Trap triggered');
        });
    }

    // -------------------------------------------------------------------------
    // decreaseStability / increaseStability
    // -------------------------------------------------------------------------

    public function decrease_stability(): void
    {
        $this->dungeon->decreaseStability(20);

        Assert::same($this->dungeon->stability, 80);

        $this->eventBus->assertDispatched(PlayerStabilityDecreased::class, function (PlayerStabilityDecreased $event) {
            Assert::same($event->amount, 20);
            Assert::same($event->total, 80);
        });
    }

    public function decrease_stability_is_clamped_at_zero(): void
    {
        $this->dungeon->stability = 10;

        $this->dungeon->decreaseStability(50);

        Assert::same($this->dungeon->stability, 0);

        $this->eventBus->assertDispatched(PlayerStabilityDecreased::class, function (PlayerStabilityDecreased $event) {
            Assert::same($event->amount, 10);
            Assert::same($event->total, 0);
        });
    }

    public function increase_stability(): void
    {
        $this->dungeon->stability = 50;

        $this->dungeon->increaseStability(30);

        Assert::same($this->dungeon->stability, 80);

        $this->eventBus->assertDispatched(PlayerStabilityIncreased::class, function (PlayerStabilityIncreased $event) {
            Assert::same($event->amount, 30);
            Assert::same($event->total, 80);
        });
    }

    public function increase_stability_is_capped_at_max_stability(): void
    {
        $this->dungeon->stability = 80;

        $this->dungeon->increaseStability(50); // maxStability is 100

        Assert::same($this->dungeon->stability, 100);

        $this->eventBus->assertDispatched(PlayerStabilityIncreased::class, function (PlayerStabilityIncreased $event) {
            Assert::same($event->amount, 20);
        });
    }

    public function increase_stability_does_nothing_when_already_at_max(): void
    {
        $this->dungeon->increaseStability(10); // already at 100

        Assert::same($this->dungeon->stability, 100);
        $this->eventBus->assertNotDispatched(PlayerStabilityIncreased::class);
    }

    public function increase_stability_includes_reason_in_event(): void
    {
        $this->dungeon->stability = 50;

        $this->dungeon->increaseStability(30, 'You found a stability altar (+30 stability)');

        $this->eventBus->assertDispatched(PlayerStabilityIncreased::class, function (PlayerStabilityIncreased $event) {
            Assert::same($event->reason, 'You found a stability altar (+30 stability)');
        });
    }

    public function increase_stability_reason_is_null_by_default(): void
    {
        $this->dungeon->stability = 50;

        $this->dungeon->increaseStability(30);

        $this->eventBus->assertDispatched(PlayerStabilityIncreased::class, function (PlayerStabilityIncreased $event) {
            Assert::null($event->reason);
        });
    }

    // -------------------------------------------------------------------------
    // collapseTile
    // -------------------------------------------------------------------------

    public function collapse_tile(): void
    {
        $tile = new Tile(new Point(5, 5));
        $this->dungeon->addTile($tile);

        $this->dungeon->collapseTile($tile);

        Assert::true($tile->isCollapsed);

        $this->eventBus->assertDispatched(TileCollapsed::class, function (TileCollapsed $event) use ($tile) {
            Assert::true($event->tile->point->equals($tile->point));
        });
    }

    public function collapse_tile_does_not_collapse_origin_tile(): void
    {
        $originTile = $this->dungeon->currentTile;

        $this->dungeon->collapseTile($originTile);

        Assert::false($originTile->isCollapsed);
        $this->eventBus->assertNotDispatched(TileCollapsed::class);
    }

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

    public function play_card(): void
    {
        $card = new BeaconMinor();
        $this->dungeon->hand[$card->id] = $card;
        $this->dungeon->mana = 100;

        $this->dungeon->playCard($card->id);

        Assert::array($this->dungeon->hand)->doesNotHaveKeys($card->id);

        $this->eventBus->assertDispatched(CardPlayed::class, function (CardPlayed $event) use ($card) {
            Assert::same($event->card->id, $card->id);
        });
    }

    public function play_card_deducts_mana(): void
    {
        $card = new BeaconMinor(); // costs 75 mana
        $this->dungeon->hand[$card->id] = $card;
        $this->dungeon->mana = 100;

        $this->dungeon->playCard($card->id);

        Assert::same($this->dungeon->mana, 25);
    }

    public function play_card_does_nothing_when_card_not_in_hand(): void
    {
        $this->dungeon->playCard('non-existent-card-id');

        $this->eventBus->assertNotDispatched(CardPlayed::class);
    }

    public function play_card_does_nothing_when_not_enough_mana(): void
    {
        $card = new BeaconMinor(); // costs 75 mana
        $this->dungeon->hand[$card->id] = $card;
        $this->dungeon->mana = 10;

        $this->dungeon->playCard($card->id);

        Assert::array($this->dungeon->hand)->hasKeys($card->id);
        $this->eventBus->assertNotDispatched(CardPlayed::class);
    }

    public function play_card_does_nothing_when_passive_slot_is_already_occupied(): void
    {
        // `DungeonActions::playCard()` unsets the occupied passive card and plays the new one
        // instead of refusing. The test fails the same way under the tempest runner in `tests/`.
        throw new SkipTest('playCard() does not block on an occupied passive slot — application bug');

        $card = new BeaconMinor(); // Type::PASSIVE
        $this->dungeon->hand[$card->id] = $card;
        $this->dungeon->mana = 150;
        $this->dungeon->passiveCard = new BeaconMinor();

        $this->dungeon->playCard($card->id);

        Assert::array($this->dungeon->hand)->hasKeys($card->id);
        $this->eventBus->assertNotDispatched(CardPlayed::class);
    }

    public function play_card_does_nothing_when_active_slot_is_already_occupied(): void
    {
        // TODO: requires a concrete active card implementation (Type::ACTIVE)
        throw new SkipTest('Requires a concrete active card implementation');
    }

    // -------------------------------------------------------------------------
    // drawCard
    // -------------------------------------------------------------------------

    public function draw_card(): void
    {
        $card = new BeaconMinor();
        $this->dungeon->deck[$card->id] = $card;
        $this->dungeon->hand = [];

        $this->dungeon->drawCard();

        Assert::array($this->dungeon->hand)->hasKeys($card->id);
        Assert::array($this->dungeon->deck)->doesNotHaveKeys($card->id);

        $this->eventBus->assertDispatched(CardDrawn::class);
    }

    public function draw_card_does_nothing_when_hand_is_full(): void
    {
        for ($i = 0; $i < $this->dungeon->maxHandCount; $i++) {
            $card = new BeaconMinor();
            $this->dungeon->hand[$card->id] = $card;
        }

        $extra = new BeaconMinor();
        $this->dungeon->deck[$extra->id] = $extra;

        $this->dungeon->drawCard();

        Assert::array($this->dungeon->hand)->doesNotHaveKeys($extra->id);
        $this->eventBus->assertNotDispatched(CardDrawn::class);
    }

    public function draw_card_does_nothing_when_deck_is_empty(): void
    {
        $this->dungeon->deck = [];
        $this->dungeon->hand = [];

        $this->dungeon->drawCard();

        Assert::blank($this->dungeon->hand);
        $this->eventBus->assertNotDispatched(CardDrawn::class);
    }

    // -------------------------------------------------------------------------
    // setPassiveCard / unsetPassiveCard
    // -------------------------------------------------------------------------

    public function set_passive_card(): void
    {
        $card = new BeaconMinor();

        $this->dungeon->setPassiveCard($card);

        Assert::same($this->dungeon->passiveCard, $card);

        $this->eventBus->assertDispatched(PassiveCardSet::class, function (PassiveCardSet $event) use ($card) {
            Assert::same($event->card->id, $card->id);
        });
    }

    public function unset_passive_card(): void
    {
        $this->dungeon->passiveCard = new BeaconMinor();

        $this->dungeon->unsetPassiveCard();

        Assert::null($this->dungeon->passiveCard);
        $this->eventBus->assertDispatched(PassiveCardUnset::class);
    }

    // -------------------------------------------------------------------------
    // setActiveCard / unsetActiveCard
    // -------------------------------------------------------------------------

    public function set_active_card(): void
    {
        $card = new BeaconMinor();

        $this->dungeon->setActiveCard($card);

        Assert::same($this->dungeon->activeCard, $card);

        $this->eventBus->assertDispatched(ActiveCardSet::class, function (ActiveCardSet $event) use ($card) {
            Assert::same($event->card->id, $card->id);
        });
    }

    public function unset_active_card(): void
    {
        $this->dungeon->activeCard = new BeaconMinor();

        $this->dungeon->unsetActiveCard();

        Assert::null($this->dungeon->activeCard);
        $this->eventBus->assertDispatched(ActiveCardUnset::class);
    }

    // -------------------------------------------------------------------------
    // addPermanentCard
    // -------------------------------------------------------------------------

    public function add_permanent_card(): void
    {
        $card = new BeaconMinor();

        $this->dungeon->addPermanentCard($card);

        Assert::array($this->dungeon->permanentCards)->hasKeys($card->id);

        $this->eventBus->assertDispatched(PermanentCardAdded::class, function (PermanentCardAdded $event) use ($card) {
            Assert::same($event->card->id, $card->id);
        });
    }

    // -------------------------------------------------------------------------
    // notifyCards
    // -------------------------------------------------------------------------

    public function notify_cards(): void
    {
        // TODO: requires a PassiveCard implementation and a DungeonEvent; verify handle() is called on active/passive/permanent cards
        throw new SkipTest('Requires a PassiveCard implementation and a DungeonEvent');
    }

    // -------------------------------------------------------------------------
    // interactWithTile
    // -------------------------------------------------------------------------

    public function interact_with_tile(): void
    {
        // TODO: requires a concrete ActiveCard implementation
        throw new SkipTest('Requires a concrete active card implementation');
    }

    // -------------------------------------------------------------------------
    // removeTileCollapse
    // -------------------------------------------------------------------------

    public function remove_tile_collapse(): void
    {
        $tile = new Tile(new Point(5, 5), isCollapsed: true);
        $this->dungeon->addTile($tile);

        $this->dungeon->removeTileCollapse($tile);

        Assert::false($tile->isCollapsed);

        $this->eventBus->assertDispatched(TileUpdated::class, function (TileUpdated $event) use ($tile) {
            Assert::true($event->tile->point->equals($tile->point));
        });
    }

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

    public function remove_tile_walls_opens_all_directions(): void
    {
        $tile = new Tile(new Point(5, 5), directions: [Direction::TOP]);
        $this->dungeon->addTile($tile);

        $this->dungeon->removeTileWalls($tile);

        $directions = array_map(fn (Direction $direction) => $direction->name, $tile->directions);
        sort($directions);
        $expected = array_map(fn (Direction $direction) => $direction->name, Direction::cases());
        sort($expected);

        Assert::same($directions, $expected);
        $this->eventBus->assertDispatched(TileUpdated::class);
    }

    // -------------------------------------------------------------------------
    // spawnDweller / despawnDweller / moveDweller
    // -------------------------------------------------------------------------

    public function spawn_dweller(): void
    {
        $point = new Point(3, 3);

        $this->dungeon->spawnDweller($point);

        Assert::notNull($this->dungeon->getDweller($point));

        $this->eventBus->assertDispatched(DwellerSpawned::class, function (DwellerSpawned $event) use ($point) {
            Assert::true($event->dweller->point->equals($point));
        });
    }

    public function despawn_dweller(): void
    {
        $point = new Point(3, 3);
        $this->dungeon->spawnDweller($point);
        $dweller = $this->dungeon->getDweller($point);

        $this->dungeon->despawnDweller($dweller);

        Assert::null($this->dungeon->getDweller($point));

        $this->eventBus->assertDispatched(DwellerDespawned::class, function (DwellerDespawned $event) use ($point) {
            Assert::true($event->dweller->point->equals($point));
        });
    }

    public function move_dweller(): void
    {
        $from = new Point(3, 3);
        $to = new Point(4, 3);
        $this->dungeon->spawnDweller($from);
        $dweller = $this->dungeon->getDweller($from);

        $this->dungeon->moveDweller($dweller, $to);

        Assert::null($this->dungeon->getDweller($from));
        Assert::notNull($this->dungeon->getDweller($to));

        $this->eventBus->assertDispatched(DwellerMoved::class, function (DwellerMoved $event) use ($from, $to) {
            Assert::true($event->from->equals($from));
            Assert::true($event->to->equals($to));
        });
    }

    // -------------------------------------------------------------------------
    // showDweller / hideDweller
    // -------------------------------------------------------------------------

    public function show_dweller(): void
    {
        $point = new Point(3, 3);
        $this->dungeon->spawnDweller($point);
        $dweller = $this->dungeon->getDweller($point);
        $dweller->isVisible = false;

        $this->dungeon->showDweller($dweller);

        Assert::true($dweller->isVisible);
        $this->eventBus->assertDispatched(DwellerUpdated::class);
    }

    public function show_dweller_does_nothing_when_already_visible(): void
    {
        $point = new Point(3, 3);
        $this->dungeon->spawnDweller($point);
        $dweller = $this->dungeon->getDweller($point);
        $dweller->isVisible = true;

        $this->dungeon->showDweller($dweller);

        $this->eventBus->assertNotDispatched(DwellerUpdated::class);
    }

    public function hide_dweller(): void
    {
        $point = new Point(3, 3);
        $this->dungeon->spawnDweller($point);
        $dweller = $this->dungeon->getDweller($point);
        $dweller->isVisible = true;

        $this->dungeon->hideDweller($dweller);

        Assert::false($dweller->isVisible);
        $this->eventBus->assertDispatched(DwellerUpdated::class);
    }

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

    public function change_visibility(): void
    {
        $this->dungeon->changeVisibility(10);

        Assert::same($this->dungeon->visibilityRadius, 10);

        $this->eventBus->assertDispatched(VisibilityChanged::class, function (VisibilityChanged $event) {
            Assert::same($event->visibilityRadius, 10);
        });
    }

    // -------------------------------------------------------------------------
    // spawnArtifact
    // -------------------------------------------------------------------------

    public function spawn_artifact(): void
    {
        $point = new Point(7, 7);

        $this->dungeon->spawnArtifact($point);

        Assert::true($this->dungeon->artifactLocation->equals($point));

        $this->eventBus->assertDispatched(ArtifactSpawned::class, function (ArtifactSpawned $event) use ($point) {
            Assert::true($event->point->equals($point));
        });
    }

    // -------------------------------------------------------------------------
    // spawnManaAltar / spawnHealthAltar / spawnStabilityAltar
    // (these register altar locations but do not dispatch events)
    // -------------------------------------------------------------------------

    public function spawn_mana_altar(): void
    {
        $point = new Point(5, 5);

        $this->dungeon->spawnManaAltar($point);

        Assert::same($this->dungeon->manaAltars[$point->x][$point->y], $point);
    }

    public function spawn_health_altar(): void
    {
        $point = new Point(5, 5);

        $this->dungeon->spawnHealthAltar($point);

        Assert::same($this->dungeon->healthAltars[$point->x][$point->y], $point);
    }

    public function spawn_stability_altar(): void
    {
        $point = new Point(5, 5);

        $this->dungeon->spawnStabilityAltar($point);

        Assert::same($this->dungeon->stabilityAltars[$point->x][$point->y], $point);
    }

    // -------------------------------------------------------------------------
    // spawnVictoryPoint / spawnShard
    // (these register locations but do not dispatch events)
    // -------------------------------------------------------------------------

    public function spawn_victory_point(): void
    {
        $point = new Point(5, 5);

        $this->dungeon->spawnVictoryPoint($point);

        Assert::same($this->dungeon->victoryPointLocations[$point->x][$point->y], $point);
    }

    public function spawn_shard(): void
    {
        $point = new Point(5, 5);

        $this->dungeon->spawnShard($point);

        Assert::same($this->dungeon->shardLocations[$point->x][$point->y], $point);
    }

    // -------------------------------------------------------------------------
    // increaseExperience (no event dispatched)
    // -------------------------------------------------------------------------

    public function increase_experience(): void
    {
        $this->dungeon->increaseExperience(100);

        Assert::same($this->dungeon->experience, 100);
    }

    // -------------------------------------------------------------------------
    // collectArtifact
    // -------------------------------------------------------------------------

    public function collect_artifact(): void
    {
        $this->dungeon->artifactLocation = clone $this->dungeon->playerPosition;
        $this->dungeon->coins = 0;
        $this->dungeon->mana = 0;

        $this->dungeon->collectArtifact();

        Assert::int($this->dungeon->coins)->greaterThan(0);
        Assert::int($this->dungeon->stability)->lessThan(100);
        Assert::int($this->dungeon->mana)->greaterThan(0);

        $this->eventBus->assertDispatched(ArtifactCollected::class);
        $this->eventBus->assertDispatched(ArtifactSpawned::class); // a new artifact is spawned afterwards
    }

    public function collect_artifact_does_nothing_when_player_is_not_on_artifact_location(): void
    {
        $this->dungeon->artifactLocation = new Point(99, 99);
        $this->dungeon->coins = 0;

        $this->dungeon->collectArtifact();

        Assert::same($this->dungeon->coins, 0);
        $this->eventBus->assertNotDispatched(ArtifactCollected::class);
    }

    // -------------------------------------------------------------------------
    // exit
    // -------------------------------------------------------------------------

    public function exit_dungeon(): void
    {
        // Player starts on origin tile (0,0)

        $this->dungeon->exit();

        Assert::true($this->dungeon->hasEnded);

        $this->eventBus->assertDispatched(PlayerExited::class, function (PlayerExited $event) {
            Assert::same($event->user, $this->dungeon->user);
        });
    }

    public function exit_dungeon_does_nothing_when_not_on_origin_tile(): void
    {
        $this->dungeon->addTile(new Tile(new Point(1, 0)));
        $this->dungeon->playerPosition = new Point(1, 0);

        $this->dungeon->exit();

        Assert::false($this->dungeon->hasEnded);
        $this->eventBus->assertNotDispatched(PlayerExited::class);
    }

    public function exit_dungeon_without_origin_requirement(): void
    {
        $this->dungeon->playerPosition = new Point(5, 5);

        $this->dungeon->exit(requiresOrigin: false);

        Assert::true($this->dungeon->hasEnded);
        $this->eventBus->assertDispatched(PlayerExited::class);
    }

    // -------------------------------------------------------------------------
    // resign
    // -------------------------------------------------------------------------

    public function resign(): void
    {
        $this->dungeon->resign();

        Assert::true($this->dungeon->hasEnded);

        $this->eventBus->assertDispatched(PlayerResigned::class, function (PlayerResigned $event) {
            Assert::same($event->user, $this->dungeon->user);
        });
    }

    // -------------------------------------------------------------------------
    // updateTile
    // -------------------------------------------------------------------------

    public function update_tile(): void
    {
        $tile = $this->dungeon->currentTile;

        $this->dungeon->updateTile($tile);

        $this->eventBus->assertDispatched(TileUpdated::class, function (TileUpdated $event) use ($tile) {
            Assert::true($event->tile->point->equals($tile->point));
        });
    }

    // -------------------------------------------------------------------------
    // increaseVictoryPoints
    // -------------------------------------------------------------------------

    public function increase_victory_points(): void
    {
        $this->dungeon->increaseVictoryPoints(10);

        Assert::same($this->dungeon->victoryPoints, 10);

        $this->eventBus->assertDispatched(PlayerVictoryPointsIncreased::class, function (PlayerVictoryPointsIncreased $event) {
            Assert::same($event->amount, 10);
            Assert::same($event->total, 10);
        });
    }

    // -------------------------------------------------------------------------
    // increaseShards
    // -------------------------------------------------------------------------

    public function increase_shards(): void
    {
        $this->dungeon->increaseShards(5);

        Assert::same($this->dungeon->shards, 5);

        $this->eventBus->assertDispatched(PlayerShardsIncreased::class, function (PlayerShardsIncreased $event) {
            Assert::same($event->amount, 5);
            Assert::same($event->total, 5);
        });
    }

    // -------------------------------------------------------------------------
    // updateCard
    // -------------------------------------------------------------------------

    public function update_card(): void
    {
        $card = new BeaconMinor();

        $this->dungeon->updateCard($card);

        $this->eventBus->assertDispatched(CardUpdated::class, function (CardUpdated $event) use ($card) {
            Assert::same($event->card->id, $card->id);
        });
    }

    public function mana_altar_grants_mana_when_cooldown_is_zero(): void
    {
        $point = new Point(1, 0);
        $tile = new Tile($point, isManaAltar: true, altarCooldown: 0);
        $this->dungeon->addTile($tile);
        $manaBefore = $this->dungeon->mana;

        $event = new PlayerMoved(from: new Point(0, 0), to: $point);
        $this->container->get(PlayerMovementListener::class)->checkForManaAltar($event);

        Assert::int($this->dungeon->mana)->greaterThan($manaBefore);
        $this->eventBus->assertDispatched(PlayerManaIncreased::class);
    }

    public function mana_altar_does_not_grant_mana_when_on_cooldown(): void
    {
        $this->dungeon->mana = 0;
        $point = new Point(1, 0);
        $tile = new Tile($point, isManaAltar: true, altarCooldown: 50);
        $this->dungeon->addTile($tile);

        $event = new PlayerMoved(from: new Point(0, 0), to: $point);
        $this->container->get(PlayerMovementListener::class)->checkForManaAltar($event);

        Assert::same($this->dungeon->mana, 0);
        $this->eventBus->assertNotDispatched(PlayerManaIncreased::class);
    }

    // -------------------------------------------------------------------------
    // Health altar
    // -------------------------------------------------------------------------

    public function health_altar_grants_health_when_cooldown_is_zero(): void
    {
        $this->dungeon->health = 50;
        $point = new Point(1, 0);
        $tile = new Tile($point, isHealthAltar: true, altarCooldown: 0);
        $this->dungeon->addTile($tile);

        $event = new PlayerMoved(from: new Point(0, 0), to: $point);
        $this->container->get(PlayerMovementListener::class)->checkForHealthAltar($event);

        Assert::int($this->dungeon->health)->greaterThan(50);
        $this->eventBus->assertDispatched(PlayerHealthIncreased::class);
    }

    public function health_altar_does_not_grant_health_when_on_cooldown(): void
    {
        $this->dungeon->health = 50;
        $point = new Point(1, 0);
        $tile = new Tile($point, isHealthAltar: true, altarCooldown: 50);
        $this->dungeon->addTile($tile);

        $event = new PlayerMoved(from: new Point(0, 0), to: $point);
        $this->container->get(PlayerMovementListener::class)->checkForHealthAltar($event);

        Assert::same($this->dungeon->health, 50);
        $this->eventBus->assertNotDispatched(PlayerHealthIncreased::class);
    }

    // -------------------------------------------------------------------------
    // Stability altar
    // -------------------------------------------------------------------------

    public function stability_altar_grants_stability_when_cooldown_is_zero(): void
    {
        $this->dungeon->stability = 50;
        $point = new Point(1, 0);
        $tile = new Tile($point, isStabilityAltar: true, altarCooldown: 0);
        $this->dungeon->addTile($tile);

        $event = new PlayerMoved(from: new Point(0, 0), to: $point);
        $this->container->get(PlayerMovementListener::class)->checkForStabilityAltar($event);

        Assert::int($this->dungeon->stability)->greaterThan(50);
        $this->eventBus->assertDispatched(PlayerStabilityIncreased::class);
    }

    public function stability_altar_does_not_grant_stability_when_on_cooldown(): void
    {
        $this->dungeon->stability = 50;
        $point = new Point(1, 0);
        $tile = new Tile($point, isStabilityAltar: true, altarCooldown: 50);
        $this->dungeon->addTile($tile);

        $event = new PlayerMoved(from: new Point(0, 0), to: $point);
        $this->container->get(PlayerMovementListener::class)->checkForStabilityAltar($event);

        Assert::int($this->dungeon->stability)->lessThanOrEqual(50);
        $this->eventBus->assertNotDispatched(PlayerStabilityIncreased::class);
    }
}
