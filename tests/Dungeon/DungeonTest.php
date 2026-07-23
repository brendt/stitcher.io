<?php

namespace Tests\Dungeon;

use App\Dungeon\Dungeon;
use App\Dungeon\Repositories\DeckRepository;
use App\Dungeon\Repositories\StatsRepository;
use App\Dungeon\Support\DungeonInitializer;
use App\Support\Authentication\User;
use Tempest\Auth\Authentication\Authenticator;
use Tempest\Container\Container;
use Tempest\Container\GenericContainer;
use Tempest\Database\Connection\Connection;
use Tempest\Database\Database;
use Tempest\Database\DatabaseInitializer;
use Tempest\EventBus\EventBus;
use Tempest\Http\GenericRequest;
use Tempest\Http\Method;
use Tempest\Http\Request;
use Tempest\Testing\After;
use Tempest\Testing\Before;
use Tempest\Testing\Testers\Database\DatabaseTester;
use Tempest\Testing\Testers\Database\TestingDatabaseInitializer;

abstract class DungeonTest
{
    use DungeonAssertions;

    protected Dungeon $dungeon;
    protected User $user;
    protected Container $container;
    protected DatabaseTester $database;
    protected TestingEventBus $eventBus;
    private EventBus $originalEventBus;
    private array $originalSingletons = [];
    private array $originalDynamicInitializers = [];

    #[Before]
    public function setUp(Container $container): void
    {
        $this->setUpDungeon($container, preventEventHandling: true);
    }

    #[After]
    public function tearDown(): void
    {
        if ($this->container instanceof GenericContainer) {
            $this->container->setSingletons($this->originalSingletons);
            $this->container->setDynamicInitializers($this->originalDynamicInitializers);
        }

        if (isset($this->originalEventBus)) {
            $this->container->singleton(EventBus::class, $this->originalEventBus);
        }
    }

    protected function setUpDungeon(Container $container, bool $preventEventHandling): void
    {
        $this->container = $container;

        if ($container instanceof GenericContainer) {
            $this->originalSingletons = $container->getSingletons();
            $this->originalDynamicInitializers = $container->getDynamicInitializers();

            $container->unregister(Database::class, tagged: true);
            $container->unregister(Connection::class, tagged: true);
            $container->removeInitializer(DatabaseInitializer::class);
            $container->addInitializer(TestingDatabaseInitializer::class);
        }

        $this->database = new DatabaseTester($container);

        // Migrate DB
        $this->database->migrate();

        // Setup request
        $request = new GenericRequest(Method::GET, '/', []);
        $this->container->singleton(Request::class, $request);
        $this->container->singleton(GenericRequest::class, $request);

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
        $this->originalEventBus = $container->get(EventBus::class);
        $this->eventBus = new TestingEventBus($this->originalEventBus);
        $container->singleton(EventBus::class, $this->eventBus);
        $this->eventBus->recordEventDispatches($preventEventHandling);
    }
}
