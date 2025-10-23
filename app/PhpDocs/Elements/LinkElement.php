<?php

namespace App\PhpDocs\Elements;

use App\PhpDocs\Element;

final readonly class LinkElement implements Element
{
    public function __construct(
        private string $text,
        private ?string $href,
    ) {}

    public function render(): string
    {
        $href = $this->href ?? '#';

        return "[$this->text]($href)";
    }
}