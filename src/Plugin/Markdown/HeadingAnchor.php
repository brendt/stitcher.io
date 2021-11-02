<?php

namespace Brendt\Stitcher\Plugin\Markdown;

use League\CommonMark\Block\Element\AbstractBlock;
use League\CommonMark\Block\Element\Heading;
use League\CommonMark\Block\Renderer\BlockRendererInterface;
use League\CommonMark\ElementRendererInterface;
use League\CommonMark\HtmlElement;

class HeadingAnchor implements BlockRendererInterface
{
    public function render(AbstractBlock $block, ElementRendererInterface $htmlRenderer, $inTightList = false)
    {
        if (!($block instanceof Heading)) {
            throw new \InvalidArgumentException('Incompatible block type: ' . \get_class($block));
        }

        $level = $block->getLevel();

        $tag = 'h' . $level;

        $attrs = $block->getData('attributes', []);

        $block = new HtmlElement($tag, $attrs, $htmlRenderer->renderInlines($block->children()));

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
