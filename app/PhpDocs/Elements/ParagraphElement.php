<?php

namespace App\PhpDocs\Elements;

use App\PhpDocs\Element;
use App\PhpDocs\HasChildren;

final class ParagraphElement implements Element, HasChildren
{
    public array $children = [];

    public function render(): string
    {
        return '<p>' . implode('', array_map(fn (Element $element) => $element->render(), $this->children)) . '</p>';
    }
}