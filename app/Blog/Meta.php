<?php

namespace App\Blog;

final class Meta
{
    public function __construct(
        public ?string $title = null,
        public ?string $description = null,
        public ?string $image = null,
        public ?string $author = null,
        public ?string $canonical = null,
    ) {}
}