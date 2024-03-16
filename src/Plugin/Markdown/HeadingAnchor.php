<?php

namespace Brendt\Stitcher\Plugin\Markdown;

use InvalidArgumentException;
use League\CommonMark\Extension\CommonMark\Node\Block\Heading;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Util\HtmlElement;
use function get_class;

class HeadingAnchor implements NodeRendererInterface
{
    public function render(Node $node, ChildNodeRendererInterface $childRenderer)
    {
        if (!($node instanceof Heading)) {
            throw new InvalidArgumentException('Incompatible block type: ' . get_class($node));
        }

        $level = $node->getLevel();

        $tag = 'h' . $level;

//        $attrs = $node->getData('attributes', []);

        $block = new HtmlElement($tag, [], $childRenderer->renderNodes($node->children()));

        if ($level > 4) {
            return $block;
        }

        $slug = $this->slug($block->getContents());

        $block->setAttribute('id', $slug);

        $contents = $block->getContents();

        $contents = "<a href=\"#{$slug}\" class=\"heading-anchor\">#</a> {$contents}";

        $block->setContents($contents);

        return $block;
    }

    private function slug(string $contents): string
    {
        return strtolower(
            str_replace(' ', '-',
                strip_tags($contents)
            )
        );
    }
}
