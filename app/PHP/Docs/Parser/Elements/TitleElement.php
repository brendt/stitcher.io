<?php

namespace App\Php\Docs\Parser\Elements;

use App\Php\Docs\Parser\Element;

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