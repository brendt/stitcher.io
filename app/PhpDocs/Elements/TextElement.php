<?php

namespace App\PhpDocs\Elements;

use App\PhpDocs\Element;
use function Tempest\Support\str;

final class TextElement implements Element
{
    public function __construct(
        private string $text,
    ) {}

    public function render(): string
    {
        return str($this->text)
            ->replace(PHP_EOL, '')
            ->replaceRegex('/\s{2,}/', ' ');
    }
}