<?php

namespace Brendt\Stitcher\Plugin\Markdown;

use InvalidArgumentException;
use League\CommonMark\Block\Element\AbstractBlock;
use League\CommonMark\Block\Element\FencedCode;
use League\CommonMark\ElementRendererInterface;
use Spatie\CommonMarkHighlighter\FencedCodeRenderer;

class HighlightCodeBlockRenderer extends FencedCodeRenderer
{
    public function render(AbstractBlock $block, ElementRendererInterface $htmlRenderer, $inTightList = false)
    {
        if (! $block instanceof FencedCode) {
            throw new InvalidArgumentException('Block must be instance of ' . FencedCode::class);
        }

        $element = parent::render($block, $htmlRenderer, $inTightList);

        $content = $element->getContents();

        $content = str_replace('&lt;/<span class="hljs-title">hljs</span>&gt;', '</span>', $content);
        $content = str_replace('&lt;/hljs&gt;', '</span>', $content);

        $lines = explode(PHP_EOL, $content);

        $regex = '/\&lt\;hljs([\w\s]+)&gt;/';

        foreach ($lines as $index => $line) {
            preg_match($regex, $line, $matches);

            $class = $matches[1] ?? '';

            $line = preg_replace($regex, "<span class=\"hljs-highlight {$class}\">", $line);

            $lines[$index] = $line;
        }

        $element->setContents(implode(PHP_EOL, $lines));

        return $element;
    }
}
