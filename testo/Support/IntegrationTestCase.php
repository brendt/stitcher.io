<?php

declare(strict_types=1);

namespace Tests\Testo\Support;

use Tempest\Console\Output\MemoryOutputBuffer;
use Tempest\Console\Output\StdoutOutputBuffer;
use Tempest\Console\OutputBuffer;
use Tempest\Container\GenericContainer;
use Tempest\Core\FrameworkKernel;
use Tempest\Database\Connection\Connection;
use Tempest\Database\Database;
use Tempest\Database\DatabaseInitializer;
use Tempest\Discovery\DiscoveryLocation;
use Tempest\EventBus\EventBus;
use Tempest\Http\GenericRequest;
use Tempest\Http\Method;
use Tempest\Http\Request;
use Testo\Lifecycle\AfterTest;
use Testo\Lifecycle\BeforeTest;

use function Tempest\env;

/**
 * Boots the Tempest framework kernel for Testo test cases.
 *
 * Under `tempest/testing` the runner boots the kernel and injects the container into `#[Before]`
 * hooks. Testo knows nothing about Tempest, so the kernel is booted here instead — once per
 * process, with the container's singletons and initializers restored after every test.
 */
abstract class IntegrationTestCase
{
    private static ?FrameworkKernel $kernel = null;

    protected GenericContainer $container;

    protected DatabaseTester $database;

    protected ConsoleTester $console;

    private EventBus $originalEventBus;

    private array $originalSingletons = [];

    private array $originalDynamicInitializers = [];

    #[BeforeTest(priority: 1000)]
    public function setUpTempest(): void
    {
        $root = dirname(__DIR__, 2);

        self::$kernel ??= FrameworkKernel::boot(
            root: $root,
            discoveryLocations: [
                new DiscoveryLocation('Tests\\Testo\\', dirname(__DIR__)),
            ],
            internalStorage: $root . '/.tempest/testo_internal_storage/' . env('TEST_TOKEN', 'default'),
        );

        /** @var GenericContainer $container */
        $container = self::$kernel->container;

        GenericContainer::setInstance($container);

        $this->container = $container;

        $this->originalSingletons = $container->getSingletons();
        $this->originalDynamicInitializers = $container->getDynamicInitializers();
        $this->originalEventBus = $container->get(EventBus::class);

        $this->setUpDatabase()->setUpConsole()->setUpBaseRequest();
    }

    #[AfterTest(priority: -1000)]
    public function tearDownTempest(): void
    {
        $this->container->setSingletons($this->originalSingletons);
        $this->container->setDynamicInitializers($this->originalDynamicInitializers);

        // `setSingletons()` only swaps the definitions; the resolved wrapper a test left behind stays
        // reachable. `singleton()` drops both, so the next test sees the real bus again — without it
        // the `TestingEventBus` layers would stack and a muted one would keep swallowing events.
        $this->container->singleton(EventBus::class, $this->originalEventBus);
    }

    protected function setUpDatabase(): self
    {
        $this->container->unregister(Database::class, tagged: true);
        $this->container->unregister(Connection::class, tagged: true);
        $this->container->removeInitializer(DatabaseInitializer::class);
        $this->container->addInitializer(TestingDatabaseInitializer::class);

        $this->database = new DatabaseTester($this->container);

        return $this;
    }

    protected function setUpConsole(): self
    {
        $this->container->singleton(OutputBuffer::class, fn (): MemoryOutputBuffer => new MemoryOutputBuffer());
        $this->container->singleton(StdoutOutputBuffer::class, fn (): MemoryOutputBuffer => new MemoryOutputBuffer());

        $this->console = new ConsoleTester($this->container);

        return $this;
    }

    protected function setUpBaseRequest(): self
    {
        $request = new GenericRequest(Method::GET, '/', []);

        $this->container->singleton(Request::class, $request);
        $this->container->singleton(GenericRequest::class, $request);

        return $this;
    }
}
