<?php

declare(strict_types=1);

namespace Tests\Testo\Dungeon;

use App\Dungeon\Dungeon;
use App\Dungeon\Repositories\DeckRepository;
use App\Dungeon\Repositories\StatsRepository;
use App\Dungeon\Support\DungeonInitializer;
use App\Support\Authentication\User;
use Tempest\Auth\Authentication\Authenticator;
use Tempest\EventBus\EventBus;
use Testo\Lifecycle\BeforeTest;
use Tests\Testo\Support\IntegrationTestCase;

abstract class DungeonTest extends IntegrationTestCase
{
    protected Dungeon $dungeon;

    protected User $user;

    protected TestingEventBus $eventBus;

    /**
     * Whether listeners are muted while the dispatches are recorded.
     *
     * Tests that assert on a card's own effect keep them muted; tests that exercise a listener
     * (such as {@see PlayerMovementListenerTest}) let the events through.
     */
    protected bool $preventEventHandling = true;

    #[BeforeTest]
    public function setUpDungeon(): void
    {
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

        $this->eventBus = new TestingEventBus($this->container->get(EventBus::class));
        $this->container->singleton(EventBus::class, $this->eventBus);
        $this->eventBus->recordEventDispatches($this->preventEventHandling);
    }
}
