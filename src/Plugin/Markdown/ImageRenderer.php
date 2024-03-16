<?php

namespace Brendt\Stitcher\Plugin\Markdown;

use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;

class ImageRenderer extends \Pageon\Lib\Markdown\ImageRenderer
{
    public function render(Node $node, ChildNodeRendererInterface $childRenderer)
    {
        $htmlElement = parent::render($node, $childRenderer);

        if (! $htmlElement->getContents()) {
              return $htmlElement;
        }

        $htmlElement->setContents("<em class=\"small center\">{$htmlElement->getContents()}</em>");

        return $htmlElement;
    }
}
