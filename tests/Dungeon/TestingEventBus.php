<?php

namespace Tests\Dungeon;

use Closure;
use Tempest\EventBus\EventBus;
use UnitEnum;

use function Tempest\Testing\test;

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
        test($this->dispatched)->hasKey($event);

        if ($callback !== null) {
            foreach ($this->dispatched[$event] as $dispatched) {
                test($callback($dispatched))->isNot(false);
            }
        }

        return $this;
    }

    public function assertNotDispatched(string $event): self
    {
        test($this->dispatched)->missesKey($event);

        return $this;
    }
}
