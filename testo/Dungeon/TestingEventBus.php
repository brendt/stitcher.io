<?php

declare(strict_types=1);

namespace Tests\Testo\Dungeon;

use Closure;
use Tempest\EventBus\EventBus;
use Testo\Assert;
use UnitEnum;

final class TestingEventBus implements EventBus
{
    /** @var array<string, list<object|string>> */
    private array $dispatched = [];

    private bool $preventHandling = false;

    public function __construct(
        private readonly EventBus $eventBus,
    ) {}

    public function dispatch(object|string $event): void
    {
        $eventName = is_string($event) ? $event : $event::class;
        $this->dispatched[$eventName][] = $event;

        if (! $this->preventHandling) {
            $this->eventBus->dispatch($event);
        }
    }

    public function listen(Closure $handler, string|UnitEnum|null $event = null): void
    {
        $this->eventBus->listen($handler, $event);
    }

    public function preventEventHandling(): self
    {
        return $this->recordEventDispatches(preventHandling: true);
    }

    public function recordEventDispatches(bool $preventHandling = false): self
    {
        $this->preventHandling = $preventHandling;

        return $this;
    }

    public function assertDispatched(string $event, ?Closure $callback = null): self
    {
        Assert::true(
            array_key_exists($event, $this->dispatched),
            sprintf('Failed asserting that event %s was dispatched.', $event),
        );

        if ($callback !== null) {
            foreach ($this->dispatched[$event] as $dispatched) {
                Assert::notSame($callback($dispatched), false, sprintf('Assertion on dispatched %s failed.', $event));
            }
        }

        return $this;
    }

    public function assertNotDispatched(string $event): self
    {
        Assert::false(
            array_key_exists($event, $this->dispatched),
            sprintf('Failed asserting that event %s was not dispatched.', $event),
        );

        return $this;
    }
}
