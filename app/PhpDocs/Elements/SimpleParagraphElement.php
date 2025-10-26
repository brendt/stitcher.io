<?php

namespace App\PhpDocs\Elements;

use App\PhpDocs\Element;

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