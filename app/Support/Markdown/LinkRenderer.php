<?php

namespace App\Support\Markdown;

use InvalidArgumentException;
use League\CommonMark\Extension\CommonMark\Node\Inline\Link;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Util\HtmlElement;
use Pageon\Config;

final class LinkRenderer implements NodeRendererInterface
{
    public function render(Node $node, ChildNodeRendererInterface $childRenderer)
    {
        if (! $node instanceof Link) {
            throw new InvalidArgumentException('Inline must be instance of ' . Link::class);
        }

        $attributes = [];

        $url = $node->getUrl();

        if (strpos($url, '*') === 0) {
            $url = substr($url, 1);

            $attributes['target'] = '_blank';
        }

        $attributes['href'] = $url;

        return new HtmlElement(
            'a',
            $attributes,
            $childRenderer->renderNodes($node->children())
        );
    }
}