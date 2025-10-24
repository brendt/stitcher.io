<?php

namespace App\PhpDocs\Elements;

use App\PhpDocs\Element;
use Dom\Node;

final class MethodParamElement implements Element
{
    public function __construct(
        private Node $node,
    ) {}

    public function render(): string
    {
        $type = null;
        $parameter = null;

        foreach ($this->node->childNodes as $node) {
            if ($node->nodeName === 'type') {
                $type = $node->textContent;
            } elseif ($node->nodeName === 'parameter') {
                $parameter = $node->textContent;
            }
        }

        return sprintf('{:hl-type:%s:} $%s', $type, $parameter);
    }
}