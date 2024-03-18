<?php

namespace Brendt\Stitcher\Plugin\Markdown;

use InvalidArgumentException;
use League\CommonMark\Extension\CommonMark\Node\Block\FencedCode;
use League\CommonMark\Extension\CommonMark\Node\Inline\Code;
use League\CommonMark\Extension\CommonMark\Renderer\Block\FencedCodeRenderer as BaseFencedCodeRenderer;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Util\HtmlElement;
use Tempest\Highlight\Highlighter;

class HighlightInlineCodeRenderer implements NodeRendererInterface
{
    public function render(Node $node, ChildNodeRendererInterface $childRenderer)
    {
        if (! $node instanceof Code) {
            throw new InvalidArgumentException('Block must be instance of ' . Code::class);
        }

        preg_match('/^\{(?<match>[\w]+)\}(?<code>.*)/', $node->getLiteral(), $match);

        $language = $match['match'] ?? 'txt';
        $code = $match['code'] ?? $node->getLiteral();
        $code = preg_replace(['/<hljs(.*?)*>/', '/<\/hljs>/'], '', $code);

        $highlighter = new Highlighter();

        return '<code>' . $highlighter->parse($code, $language) . '</code>';
    }
}
