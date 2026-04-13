<?php

namespace App\Dungeon\Support;

use App\Dungeon\Dungeon;
use Tempest\Container\Container;
use Tempest\EventBus\EventBusMiddleware;
use Tempest\EventBus\EventBusMiddlewareCallable;

final readonly class DungeonChangeEventMiddleware implements EventBusMiddleware
{
    public function __construct(
        private Container $container,
    ) {}

    public function __invoke(object|string $event, EventBusMiddlewareCallable $next): void
    {
        $next($event);

        if (! $event instanceof DungeonEvent) {
            return;
        }

        $dungeon = $this->container->get(Dungeon::class);

        if (! $dungeon) {
            return;
        }

        $dungeon->registerChange($event->name, $event->payload);
    }
}