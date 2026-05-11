<?php

namespace App\Php\Learn;

use function Tempest\Router\uri;

final class LearnChapter
{
    public function __construct(
        public int $index,
        public string $slug,
        public string $title,
        public string $content,
    ) {}

    public string $uri {
        get => uri([LearnController::class, 'show'], chapter: $this->slug);
    }
}