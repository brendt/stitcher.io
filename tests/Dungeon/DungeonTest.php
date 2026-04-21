<?php

namespace Tests\Dungeon;

use App\Dungeon\Dungeon;
use App\Dungeon\Repositories\DeckRepository;
use App\Dungeon\Repositories\DungeonRepository;
use App\Dungeon\Repositories\StatsRepository;
use App\Dungeon\Support\DungeonInitializer;
use App\Support\Authentication\User;
use PHPUnit\Framework\Attributes\Before;
use Redis;
use Tempest\Auth\Authentication\Authenticator;
use Tests\IntegrationTestCase;

abstract class DungeonTest extends IntegrationTestCase
{
    protected Dungeon $dungeon;
    protected User $user;

    #[Before]
    public function setUp(): void
    {
        parent::setUp();

        // Migrate DB
        $this->database->migrate();

        // Setup auth
        $this->user = new User('Brent', 'example@example.com')->save();
        $this->container->get(Authenticator::class)->authenticate($this->user);

        // Setup stats
        $statsRepository = $this->container->get(StatsRepository::class);
        $statsRepository->forUser($this->user);

        // Setup dungeon
        $this->dungeon = Dungeon::new(
            user: $this->user,
            deckRepository: $this->container->get(DeckRepository::class),
            statsRepository: $statsRepository,
        );

        $this->container->singleton(Dungeon::class, $this->dungeon);
        $this->container->removeInitializer(DungeonInitializer::class);

        // Setup event bus
        $this->eventBus->preventEventHandling();
    }
}