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

        return sprintf(
            '<a href="%s">%s</a>',
            $this->href ?? '#',
            $this->text,
        );
    }
}