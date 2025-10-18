<?php

namespace App\Support\Markdown;

use App\Support\Image\ImageFactory;
use InvalidArgumentException;
use League\CommonMark\Extension\CommonMark\Node\Inline\Image;
use League\CommonMark\Node\Inline\Text;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Util\HtmlElement;
use function Tempest\Support\arr;

final readonly class ImageRenderer implements NodeRendererInterface
{
    public function __construct(
        private ImageFactory $imageFactory,
    ) {
    }

    public function render(Node $node, ChildNodeRendererInterface $childRenderer): HtmlElement
    {
        if (! $node instanceof Image) {
            throw new InvalidArgumentException('Inline must be instance of ' . Image::class);
        }

        $attributes = [];

        $image = $this->imageFactory->create($node->getUrl());

        if ($image === null) {
            return new HtmlElement('p', attributes: ['style' => 'font-weight: bold; color: red;'], contents: "Broken image: {$node->getUrl()}");
        }

        $alt = $node->firstChild();

        $attributes['src'] = $image->src;
        $attributes['srcset'] = arr($image->srcset)->map(fn($srcset) => (string) $srcset)->implode(', ')->toString();
        $attributes['sizes'] = '';
        $attributes['alt'] = $alt instanceof Text
            ? $alt->getLiteral()
            : '';

        return new HtmlElement(
            'img',
            $attributes,
            $childRenderer->renderNodes($node->children())
        );
    }
}