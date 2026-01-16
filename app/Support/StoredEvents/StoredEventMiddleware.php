<?php

declare(strict_types=1);

namespace App\Support\StoredEvents;

use DateTimeImmutable;
use Override;
use Tempest\EventBus\EventBusMiddleware;
use Tempest\EventBus\EventBusMiddlewareCallable;

final readonly class StoredEventMiddleware implements EventBusMiddleware
{
    #[Override]
    public function __invoke(string|object $event, EventBusMiddlewareCallable $next): void
    {
        if ($event instanceof ShouldBeStored) {
            new StoredEvent(
                uuid: $event->uuid,
                eventClass: $event::class,
                payload: $event->serialize(),
                createdAt: $event instanceof HasCreatedAtDate ? $event->createdAt : new DateTimeImmutable(),
            )->save();
        }

        $next($event);
    }
}
