<?php

namespace App\Php\Docs\Parser\Elements;

use App\Php\Docs\Parser\Element;
use App\Php\Docs\Parser\HasChildren;

final class NestedElement implements Element, HasChildren
{
    public array $children = [];

    public function render(): string
    {
        return implode(PHP_EOL, array_map(fn (Element $element) => $element->render(), $this->children));
    }
}