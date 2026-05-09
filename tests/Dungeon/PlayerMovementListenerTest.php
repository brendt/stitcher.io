<?php

namespace Tests\Dungeon;

use App\Dungeon\Direction;
use App\Dungeon\Dungeon;
use App\Dungeon\Events\PlayerHealthIncreased;
use App\Dungeon\Events\PlayerManaIncreased;
use App\Dungeon\Events\PlayerMoved;
use App\Dungeon\Events\PlayerStabilityIncreased;
use App\Dungeon\Events\RelicCollected;
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
    // Relic
    // -------------------------------------------------------------------------

    #[Test]
    public function moving_onto_relic_tile_collects_the_relic(): void
    {
        $this->dungeon->cheat = true;
        $this->dungeon->addTile(new Tile(new Point(1, 0), isRelic: true));

        $this->dungeon->move(Direction::RIGHT);

        $this->eventBus->assertDispatched(RelicCollected::class, function (RelicCollected $event) {
            $this->assertTrue($event->tile->point->equals(new Point(1, 0)));
            $this->assertFalse($event->tile->isRelic);
        });
    }

    #[Test]
    public function moving_onto_non_relic_tile_does_not_dispatch_relic_collected(): void
    {
        $this->dungeon->cheat = true;
        $this->dungeon->addTile(new Tile(new Point(1, 0)));

        $this->dungeon->move(Direction::RIGHT);

        $this->eventBus->assertNotDispatched(RelicCollected::class);
    }

    // -------------------------------------------------------------------------
    // Mana altar
    // -------------------------------------------------------------------------

}
