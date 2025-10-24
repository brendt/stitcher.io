<?php

namespace App\PhpDocs\Elements;

use App\PhpDocs\Element;

final readonly class InlineCodeElement implements Element
{
    public function __construct(
        private string $code,
    ) {}

    public function render(): string
    {
        return "<code>{$this->code}</code>";
    }
}