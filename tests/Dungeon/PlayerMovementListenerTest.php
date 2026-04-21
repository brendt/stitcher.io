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

}
