<?php

namespace App\Aggregate\Suggestions\Events;

final readonly class FeedUriFetchFailed
{
    public function __construct(
        public string $uri,
    ) {}
}