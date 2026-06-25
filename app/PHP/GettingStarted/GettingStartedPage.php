<?php

namespace App\PHP\GettingStarted;

use App\Blog\Meta;
use function Tempest\Router\uri;

final class GettingStartedPage
{
    public function __construct(
        public int $index,
        public string $slug,
        public string $title,
        public string $content,
        public Meta $meta,
        public ?GettingStartedPage $next = null,
    ) {}

    public string $uri {
        get => uri([GettingStartedController::class, 'show'], slug: $this->slug);
    }
}