<?php

namespace App\Aggregate\Posts\Events;

final class SourceSyncFailed
{
    public function __construct(
        public string $uri,
    ) {}
}