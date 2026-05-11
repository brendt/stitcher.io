<?php

namespace App\Php\Docs\Parser\Elements;

use App\Php\Docs\Parser\Element;
use App\Php\Docs\Parser\HasChildren;

final class DivElement implements Element, HasChildren
{
    public array $children = [];

    public function __construct(private string $name) {}

    public function render(): string
    {
        return sprintf(
            <<<'HTML'
            <div class="php-%s">%s</div>
            HTML,
            $this->name,
            implode(PHP_EOL, array_map(fn (Element $element) => $element->render(), $this->children)),
        );
    }
}