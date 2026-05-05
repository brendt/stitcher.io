<?php

namespace App\Blog\Book;

use function Tempest\Router\uri;

final class Chapter
{
    public function __construct(
        private(set) readonly string $slug,
        private(set) readonly string $index,
        private(set) readonly string $title,
        private(set) string $body,
    ) {
    }

    public string $uri {
        get => uri([BlogBookController::class, 'index']) . "?filter={$this->slug}";
    }

    public string $cover {
        get => "/img/{$this->indexAsString}-cover.svg";
    }
}