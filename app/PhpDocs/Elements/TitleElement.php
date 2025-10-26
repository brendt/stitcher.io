<?php

namespace App\PhpDocs\Elements;

use App\PhpDocs\Element;

final readonly class TitleElement implements Element
{
    public function __construct(
        private string $title,
        private int $level,
    ) {}

    public function render(): string
    {
        $title = trim($this->title);

        $level = match ($this->level) {
            1 => '1',
            default => '2',
        };

        return sprintf('<h%s>%s</h%s>', $level, $title, $level);
    }
}