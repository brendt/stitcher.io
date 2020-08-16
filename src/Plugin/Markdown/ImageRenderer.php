<?php

namespace Brendt\Stitcher\Plugin\Markdown;

use League\CommonMark\ElementRendererInterface;
use League\CommonMark\Inline\Element\AbstractInline;

class ImageRenderer extends \Pageon\Lib\Markdown\ImageRenderer
{
    public function render(AbstractInline $inline, ElementRendererInterface $htmlRenderer)
    {
        $htmlElement = parent::render($inline, $htmlRenderer);

        if (! $htmlElement->getContents()) {
              return $htmlElement;
        }

        $htmlElement->setContents("<em class=\"small center\">{$htmlElement->getContents()}</em>");

        return $htmlElement;
    }
}
