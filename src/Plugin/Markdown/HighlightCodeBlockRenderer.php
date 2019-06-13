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

        $content = preg_replace_callback('/\&lt;[\w\s\<\"\=\-\>\/]+hljs[\w\s\<\"\=\-\>\/]+/', function ($match) {
            $match = str_replace('<span class="hljs-title">', '', $match[0] ?? '');

            $match = str_replace('</span>', '', $match);

            return $match;
        }, $content);

        $content = str_replace('&lt;/hljs&gt;', '</span>', $content);

        $lines = explode(PHP_EOL, $content);

        $regex = '/\&lt\;hljs([\w\s]+)&gt;/';

        foreach ($lines as $index => $line) {
            $line = preg_replace_callback($regex, function ($matches) {
                $class = $matches[1] ?? '';

                return "<span class=\"hljs-highlight {$class}\">";
            }, $line);

            $lines[$index] = $line;
        }

        unset($lines[array_key_last($lines)]);

        $element->setContents(implode(PHP_EOL, $lines) . '</code>');

        return $element;
    }
}
