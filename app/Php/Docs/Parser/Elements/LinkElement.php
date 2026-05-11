<?php

namespace App\Php\Docs\Parser\Elements;

use App\Php\Docs\DocsController;
use App\Php\Docs\Parser\Element;
use Dom\Node;
use function Tempest\Router\uri;

final readonly class LinkElement implements Element
{
    public function __construct(
        private Node $node,
    ) {}

    public function render(): string
    {
        $text = $this->node->textContent;

        $href = '#';

        if ($this->node instanceof \Dom\Element) {
            if ($href = $this->node->getAttribute('linkend')) {
                $href = str_replace('.', '/', $href);
                $href = uri([DocsController::class, 'show'], slug: $href);
            } else {
                $href = $this->node->getAttribute('xlink:href');
            }
        }

        return sprintf(
            '<a href="%s">%s</a>',
            $href,
            $text,
        );
    }
}