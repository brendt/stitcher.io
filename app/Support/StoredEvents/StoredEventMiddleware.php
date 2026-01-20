<?php

declare(strict_types=1);

namespace App\Support\StoredEvents;

use DateTimeImmutable;
use Override;
use Tempest\Container\Container;
use Tempest\EventBus\EventBusMiddleware;
use Tempest\EventBus\EventBusMiddlewareCallable;

final readonly class StoredEventMiddleware implements EventBusMiddleware
{
    public function __construct(
        private StoredEventConfig $storedEventConfig,
    private Container $container,
    ) {}

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

        foreach ($this->storedEventConfig->projectors as $projectorClass) {
            $projector = $this->container->get($projectorClass);

            if ($projector instanceof BufferedProjector) {
                $projector->persist();
            }
        }
    }
}
