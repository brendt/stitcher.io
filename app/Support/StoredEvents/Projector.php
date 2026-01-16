<?php

declare(strict_types=1);

namespace App\Support\StoredEvents;

interface Projector
{
    public function replay(object $event): void;

    public function clear(): void;
}
