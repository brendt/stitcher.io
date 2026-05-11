<?php

namespace App\Php\Docs\Parser\Elements;

use App\Php\Docs\Parser\Element;
use App\Php\Docs\Parser\HasChildren;

final class ListItemElement implements Element, HasChildren
{
    public array $children = [];

    public function render(): string
    {
        return '<div class="php-list-item">' . implode(PHP_EOL, array_map(fn (Element $element) => $element->render(), $this->children)) . '</div>';
    }
}