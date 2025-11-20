<?php

namespace App\Aggregate\Posts\Events;

final readonly class PostSynced
{
    public function __construct(
        public string $uri,
    ) {}
}