<?php

namespace App\PhpDocs\Elements;

use App\PhpDocs\Element;
use App\PhpDocs\HasChildren;

final class VarListEntryElement implements Element, HasChildren
{
    public array $children = [];

    public function render(): string
    {
        return sprintf(<<<'HTML'
            <div class="php-var-list">
                %s
            </div>    
            HTML,
            implode(PHP_EOL, array_map(fn (Element $element) => $element->render(), $this->children))
        );
    }
}