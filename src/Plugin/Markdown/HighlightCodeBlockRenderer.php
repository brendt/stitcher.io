<?php

namespace Brendt\Stitcher\Plugin\Markdown;

use InvalidArgumentException;
use League\CommonMark\Extension\CommonMark\Node\Block\FencedCode;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Extension\CommonMark\Renderer\Block\FencedCodeRenderer as BaseFencedCodeRenderer;
use League\CommonMark\Util\HtmlElement;
use Tempest\Highlight\Highlighter;
use League\CommonMark\Renderer\NodeRendererInterface;

class HighlightCodeBlockRenderer implements NodeRendererInterface
{
    public function render(Node $node, ChildNodeRendererInterface $childRenderer)
    {

        if (! $node instanceof FencedCode) {
            throw new InvalidArgumentException('Block must be instance of ' . FencedCode::class);
        }

        $highlight = new Highlighter();
        $code = $node->getLiteral();
        // Remove hljs tags
        $code = preg_replace(['/<hljs(.*?)*>/', '/<\/hljs>/'], '', $code);
        $language = $node->getInfoWords()[0] ?? 'txt';

        return new HtmlElement(
            'pre',
            [],
            $highlight->parse($code, $language)
        );
    }
}
