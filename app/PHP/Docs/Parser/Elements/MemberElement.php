<?php

namespace App\Php\Docs\Parser\Elements;

use App\Php\Docs\DocsController;
use App\Php\Docs\Parser\Element;
use App\Php\Docs\Parser\HasChildren;
use Dom\Node;
use function Tempest\Router\uri;
use function Tempest\Support\path;

final class MemberElement implements Element, HasChildren
{
    public array $children = [];

    public function __construct(
        private string $currentSlug,
        private Node $node,
    ) {}

    public function render(): string
    {
        if ($this->node->firstChild?->nodeName === 'link') {
            return new LinkElement($this->node->firstChild)->render();
        }


        $path = pathinfo($this->currentSlug, PATHINFO_DIRNAME);

        $child = implode('', array_map(fn (Element $element) => $element->render(), $this->children));

        return sprintf(
            '<a href="%s">%s</a>',
            uri(
                [DocsController::class, 'show'],
                slug: path(
                    $path,
                    str_replace('_', '-', strip_tags($child)),
                )->toString(),
            ),
            $child,
        );
    }
}