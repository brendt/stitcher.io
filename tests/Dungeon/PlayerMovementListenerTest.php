<?php

namespace Tests\Dungeon;

use App\Dungeon\Dungeon;
use App\Dungeon\Events\PlayerHealthIncreased;
use App\Dungeon\Events\PlayerManaIncreased;
use App\Dungeon\Events\PlayerMoved;
use App\Dungeon\Events\PlayerStabilityIncreased;
use App\Dungeon\Point;
use App\Dungeon\Repositories\DeckRepository;
use App\Dungeon\Repositories\StatsRepository;
use App\Dungeon\Support\DungeonInitializer;
use App\Dungeon\Tile;
use App\Support\Authentication\User;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use Tempest\Auth\Authentication\Authenticator;
use Tests\IntegrationTestCase;
use function Tempest\EventBus\event;

final class PlayerMovementListenerTest extends IntegrationTestCase
{
    protected Dungeon $dungeon;
    protected User $user;

    #[Before]
    public function setUp(): void
    {
        parent::setUp();

        $this->database->migrate();

        $this->user = new User('Brent', 'example@example.com')->save();
        $this->container->get(Authenticator::class)->authenticate($this->user);

        $statsRepository = $this->container->get(StatsRepository::class);
        $statsRepository->forUser($this->user);

        $this->dungeon = Dungeon::new(
            user: $this->user,
            deckRepository: $this->container->get(DeckRepository::class),
            statsRepository: $statsRepository,
        );

        $this->container->singleton(Dungeon::class, $this->dungeon);
        $this->container->removeInitializer(DungeonInitializer::class);
        // Record dispatches for assertions, but still execute listeners
        $this->eventBus->recordEventDispatches(preventHandling: false);
    }

    // -------------------------------------------------------------------------
    // Mana altar
    // -------------------------------------------------------------------------

    #[Test]
    public function mana_altar_grants_mana_when_cooldown_is_zero(): void
    {
        $point = new Point(1, 0);
        $tile = new Tile($point, isManaAltar: true, altarCooldown: 0);
        $this->dungeon->addTile($tile);
        $manaBefore = $this->dungeon->mana;

        event(new PlayerMoved(from: new Point(0, 0), to: $point));

        $this->assertGreaterThan($manaBefore, $this->dungeon->mana);
        $this->eventBus->assertDispatched(PlayerManaIncreased::class);
    }

    #[Test]
    public function mana_altar_does_not_grant_mana_when_on_cooldown(): void
    {
        $this->dungeon->mana = $this->dungeon->maxMana; // prevent gainMana listener from also adding mana
        $point = new Point(1, 0);
        $tile = new Tile($point, isManaAltar: true, altarCooldown: 50);
        $this->dungeon->addTile($tile);

        event(new PlayerMoved(from: new Point(0, 0), to: $point));

        $this->assertSame($this->dungeon->maxMana, $this->dungeon->mana);
        $this->eventBus->assertNotDispatched(PlayerManaIncreased::class);
    }

    // -------------------------------------------------------------------------
    // Health altar
    // -------------------------------------------------------------------------

    #[Test]
    public function health_altar_grants_health_when_cooldown_is_zero(): void
    {
        $this->dungeon->health = 50;
        $point = new Point(1, 0);
        $tile = new Tile($point, isHealthAltar: true, altarCooldown: 0);
        $this->dungeon->addTile($tile);

        event(new PlayerMoved(from: new Point(0, 0), to: $point));

        $this->assertGreaterThan(50, $this->dungeon->health);
        $this->eventBus->assertDispatched(PlayerHealthIncreased::class);
    }

    #[Test]
    public function health_altar_does_not_grant_health_when_on_cooldown(): void
    {
        $this->dungeon->health = 50;
        $point = new Point(1, 0);
        $tile = new Tile($point, isHealthAltar: true, altarCooldown: 50);
        $this->dungeon->addTile($tile);

        event(new PlayerMoved(from: new Point(0, 0), to: $point));

        $this->assertSame(50, $this->dungeon->health);
        $this->eventBus->assertNotDispatched(PlayerHealthIncreased::class);
    }

    // -------------------------------------------------------------------------
    // Stability altar
    // -------------------------------------------------------------------------

    #[Test]
    public function stability_altar_grants_stability_when_cooldown_is_zero(): void
    {
        $this->dungeon->stability = 50;
        $point = new Point(1, 0);
        $tile = new Tile($point, isStabilityAltar: true, altarCooldown: 0);
        $this->dungeon->addTile($tile);

        event(new PlayerMoved(from: new Point(0, 0), to: $point));

        $this->assertGreaterThan(50, $this->dungeon->stability);
        $this->eventBus->assertDispatched(PlayerStabilityIncreased::class);
    }

    #[Test]
    public function stability_altar_does_not_grant_stability_when_on_cooldown(): void
    {
        $this->dungeon->stability = 50;
        $point = new Point(1, 0);
        $tile = new Tile($point, isStabilityAltar: true, altarCooldown: 50);
        $this->dungeon->addTile($tile);

        event(new PlayerMoved(from: new Point(0, 0), to: $point));

        $this->assertSame(50, $this->dungeon->stability);
        $this->eventBus->assertNotDispatched(PlayerStabilityIncreased::class);
    }
}
