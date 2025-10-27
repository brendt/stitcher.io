<?php

namespace App\PhpDocs\Elements;

use App\PhpDocs\Element;
use App\PhpDocs\PhpDocsController;
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
                $href = uri([PhpDocsController::class, 'show'], slug: $href);
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