<?php

namespace Brendt\Stitcher\Plugin\Markdown;

use League\CommonMark\Block\Element\AbstractBlock;
use League\CommonMark\Block\Renderer\HeadingRenderer;
use League\CommonMark\ElementRendererInterface;

class HeadingAnchor extends HeadingRenderer
{
    public function render(AbstractBlock $block, ElementRendererInterface $htmlRenderer, $inTightList = false)
    {
        $block = parent::render($block, $htmlRenderer, $inTightList);

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
