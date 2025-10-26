<?php

namespace App\PhpDocs\Elements;

use App\PhpDocs\Element;
use App\PhpDocs\HasChildren;
use App\PhpDocs\PhpDocsController;
use function Tempest\Router\uri;
use function Tempest\Support\path;

final class MemberElement implements Element, HasChildren
{
    public array $children = [];

    public function __construct(
        private string $currentSlug,
    ) {}

    public function render(): string
    {
        $path = pathinfo($this->currentSlug, PATHINFO_DIRNAME);

        $child = implode('', array_map(fn (Element $element) => $element->render(), $this->children));

        return sprintf(
            '<a href="%s">%s</a>',
            uri(
                [PhpDocsController::class, 'show'],
                slug: path(
                    $path,
                    str_replace('_', '-', strip_tags($child)),
                )->toString(),
            ),
            $child,
        );
    }
}