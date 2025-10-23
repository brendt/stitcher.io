<?php

namespace App\PhpDocs\Elements;

use App\PhpDocs\Element;
use App\PhpDocs\HasChildren;

final class ListElement implements Element, HasChildren
{
    public array $children = [];

    public function render(): string
    {
        return '<ul>' . implode(PHP_EOL, array_map(fn (Element $element) => $element->render(), $this->children)) . '</ul>';
    }
}