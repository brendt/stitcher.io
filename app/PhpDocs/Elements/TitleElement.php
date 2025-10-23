<?php

namespace App\PhpDocs\Elements;

use App\PhpDocs\Element;

final class TitleElement implements Element
{
    public function __construct(
        private readonly string $title,
        private readonly int $level,
    ) {}

    public function render(): string
    {
        $title = trim($this->title);

        $level = match ($this->level) {
            1 => '#',
            default => '##',
        };

        return "{$level} {$title}";
    }
}