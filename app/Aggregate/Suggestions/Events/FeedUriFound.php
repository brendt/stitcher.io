<?php

namespace App\Aggregate\Suggestions\Events;

final readonly class FeedUriFound
{
    public function __construct(
        public string $uri,
    ) {}
}