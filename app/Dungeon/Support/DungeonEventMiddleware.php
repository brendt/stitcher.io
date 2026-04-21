<?php

namespace App\Dungeon\Support;

use App\Dungeon\Dungeon;
use App\Dungeon\DungeonEvent;
use Tempest\Container\Container;
use Tempest\EventBus\EventBusMiddleware;
use Tempest\EventBus\EventBusMiddlewareCallable;

final readonly class DungeonEventMiddleware implements EventBusMiddleware
{
    public function __construct(
        private Container $container,
    ) {}

    public function __invoke(object|string $event, EventBusMiddlewareCallable $next): void
    {
        if (! $event instanceof DungeonEvent) {
            $next($event);

            return;
        }

        /** @var Dungeon $dungeon */
        $dungeon = $this->container->get(Dungeon::class);

        if (! $dungeon) {
            return;
        }

        $dungeon->registerChange($event->name, $event->payload);

        $next($event);

        $dungeon->notifyCards($event);
    }
}