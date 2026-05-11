<?php

namespace App\Php\Docs\Parser\Elements;

use App\Php\Docs\Parser\Element;

final class SimpleParagraphElement implements Element
{
    public function __construct(
        private string $text,
    ) {}

    public function render(): string
    {
        return "<p>{$this->text}</p>";
    }
}