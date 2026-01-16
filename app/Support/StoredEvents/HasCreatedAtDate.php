<?php

declare(strict_types=1);

namespace App\Support\StoredEvents;

use DateTimeImmutable;

interface HasCreatedAtDate
{
    public DateTimeImmutable $createdAt {
        get;
    }
}
