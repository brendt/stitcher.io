<?php

namespace Brendt\Stitcher\Plugin\Markdown;

use InvalidArgumentException;
use League\CommonMark\Extension\CommonMark\Node\Inline\Code;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;

class HighlightInlineCodeRenderer implements NodeRendererInterface
{
    public function render(Node $node, ChildNodeRendererInterface $childRenderer)
    {
        if (! $node instanceof Code) {
            throw new InvalidArgumentException('Block must be instance of ' . Code::class);
        }

        $content = htmlentities($node->getLiteral());

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
