<?php

namespace App\PhpDocs\Elements;

use App\PhpDocs\Element;
use App\PhpDocs\Elements\Synopsis\MethodNameElement;
use App\PhpDocs\Elements\Synopsis\TypeElement;
use Dom\Node;

final readonly class MethodSynopsisElement implements Element
{
    public function __construct(
        private Node $node,
    ) {}

    public function render(): string
    {
        $type = null;
        $methodName = null;
        $methodParams = [];

        foreach ($this->node->childNodes as $node) {
            if ($node->nodeName === 'type') {
                $type = $node->textContent;
            } elseif ($node->nodeName === 'methodname') {
                $methodName = $node->textContent;
            } elseif ($node->nodeName === 'methodparam') {
                $methodParams[] = new MethodParamElement($node);
            }
        }

        return sprintf(<<<'TXT'
            ```php
            {:hl-property:%s:}(%s): {:hl-type:%s:}
            ```
            TXT,
            $methodName,
            implode(', ', array_map(fn (MethodParamElement $methodParam) => $methodParam->render(), $methodParams)),
            $type,
        );
    }

    private function renderNode(Node $node): string
    {
        $string = '';

        if (! $node->hasChildNodes()) {
            $string .= $node->textContent;
        }

        foreach ($node->childNodes as $childNode) {
            $string .= $this->renderNode($childNode);
        }

        return $string;
    }
}