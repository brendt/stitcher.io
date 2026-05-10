<?php

namespace App\Php\Docs\Parser\Elements;

use App\Php\Docs\Parser\Element;
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