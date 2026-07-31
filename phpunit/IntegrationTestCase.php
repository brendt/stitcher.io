<?php

declare(strict_types=1);

namespace Tests\PHPUnit;

use Tempest\Container\GenericContainer;
use Tempest\Core\FrameworkKernel;
use Tempest\Discovery\DiscoveryLocation;
use Tempest\EventBus\EventBus;
use Tempest\Framework\Testing\IntegrationTest;

use function Tempest\env;

/**
 * Boots the framework kernel once per process instead of once per test.
 *
 * `IntegrationTest::setupKernel()` caches the kernel on the instance (`$this->kernel ??= …`), but
 * `tearDown()` unsets it and PHPUnit builds a fresh test-case object for every test — so the guard
 * never hits and the kernel is booted anew for each one. Caching it statically is what the tempest
 * and Testo runners do; the price is that the container survives between tests, so its singletons
 * and initializers are snapshotted here and rolled back afterwards.
 */
abstract class IntegrationTestCase extends IntegrationTest
{
    protected string $root = __DIR__ . '/../';

    private static ?FrameworkKernel $bootedKernel = null;

    private EventBus $originalEventBus;

    private array $originalSingletons = [];

    private array $originalDynamicInitializers = [];

    /**
     * `IntegrationTest::discoverTestLocations()` hardcodes `<root>/tests`, which is the Tempest
     * runner's suite — this one would otherwise discover the other suite's tests and configs and
     * never see its own. Each suite discovers its own tree, as `testo.php` does for Testo.
     */
    protected function discoverTestLocations(): array
    {
        return [new DiscoveryLocation('Tests\\PHPUnit\\', __DIR__)];
    }

    protected function setupKernel(): self
    {
        $this->internalStorage = $this->root . '/.tempest/test_internal_storage/' . env('TEST_TOKEN', 'default');

        self::$bootedKernel ??= FrameworkKernel::boot(
            root: $this->root,
            discoveryLocations: [...$this->discoveryLocations, ...$this->discoverTestLocations()],
            internalStorage: $this->internalStorage,
        );

        $this->kernel = self::$bootedKernel;

        /** @var GenericContainer $container */
        $container = $this->kernel->container;
        $this->container = $container;

        // `tearDown()` nulls the global instance, which a fresh boot would otherwise have restored.
        GenericContainer::setInstance($container);

        $this->originalSingletons = $container->getSingletons();
        $this->originalDynamicInitializers = $container->getDynamicInitializers();
        $this->originalEventBus = $container->get(EventBus::class);

        return $this;
    }

    protected function tearDown(): void
    {
        $this->container->setSingletons($this->originalSingletons);
        $this->container->setDynamicInitializers($this->originalDynamicInitializers);

        // `EventBusTester::recordEventDispatches()` wraps whatever `EventBus` the container holds in
        // a `FakeEventBus`. `setSingletons()` only swaps the definitions, so the resolved wrapper
        // stays reachable and the layers would stack — a `preventHandling: true` one from an earlier
        // test keeps swallowing events. `singleton()` drops the resolved instance too.
        $this->container->singleton(EventBus::class, $this->originalEventBus);

        parent::tearDown();
    }
}
