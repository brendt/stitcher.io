<?php

namespace Brendt\Stitcher\Plugin\Markdown;

use InvalidArgumentException;
use League\CommonMark\ElementRendererInterface;
use League\CommonMark\Inline\Element\AbstractInline;
use League\CommonMark\Inline\Element\Code;
use League\CommonMark\Inline\Renderer\InlineRendererInterface;

class HighlightInlineCodeRenderer implements InlineRendererInterface
{
    public function render(AbstractInline $inline, ElementRendererInterface $htmlRenderer)
    {
        if (! $inline instanceof Code) {
            throw new InvalidArgumentException('Block must be instance of ' . Code::class);
        }

        $content = $inline->getContent();

        if (! strpos($content, 'hljs')) {
            return '<code>' . $content . '</code>';
        }

        $content = str_replace('</hljs>', '</span>', $content);

        $regex = '/<hljs([\w\s]+)>/';

        $content = preg_replace_callback($regex, function ($matches) {
            $class = $matches[1] ?? '';

            return "<span class=\"hljs-highlight {$class}\">";
        }, $content);

        return '<code>' . $content . '</code>';
    }
}
