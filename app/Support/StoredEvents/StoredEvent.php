<?php

declare(strict_types=1);

namespace App\Support\StoredEvents;

use DateTimeImmutable;
use Tempest\Database\IsDatabaseModel;
use Tempest\Reflection\ClassReflector;

final class StoredEvent
{
    use IsDatabaseModel;

    public function __construct(
        public string $uuid,
        public string $eventClass,
        public string $payload,
        public DateTimeImmutable $createdAt = new DateTimeImmutable(),
    ) {}

    public function getEvent(): object
    {
        return new ClassReflector($this->eventClass)->callStatic('unserialize', $this->payload);
    }
}
