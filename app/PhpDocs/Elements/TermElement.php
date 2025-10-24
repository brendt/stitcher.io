<?php

namespace App\PhpDocs\Elements;

use App\PhpDocs\Element;
use App\PhpDocs\HasChildren;

final class TermElement implements Element, HasChildren
{
    public array $children = [];

    public function render(): string
    {
        return sprintf(<<<'HTML'
            <span class="php-term">%s</span>
            HTML,
            implode(PHP_EOL, array_map(fn (Element $element) => $element->render(), $this->children))
        );
    }
}