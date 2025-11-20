<?php

namespace App\Aggregate\Suggestions\Events;

final readonly class FeedUrisResolved
{
    public function __construct(
        public array $uris,
    ) {}
}