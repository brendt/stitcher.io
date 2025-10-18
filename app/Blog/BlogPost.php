<?php

namespace App\Blog;

use Tempest\DateTime\DateTime;
use Tempest\Router\Bindable;
use function Tempest\Router\uri;

final class BlogPost implements Bindable
{
    public function __construct(
        public string $slug,
        public string $title,
        public string $content,
        public DateTime $date,
        public Meta $meta,
        public ?BlogPost $next = null,
    ) {}

    public string $uri {
        get => uri([BlogController::class, 'show'], slug: $this->slug);
    }

    public static function resolve(string $input): Bindable
    {

    }
}