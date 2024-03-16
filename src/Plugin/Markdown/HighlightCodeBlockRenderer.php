<?php

namespace Brendt\Stitcher\Plugin\Markdown;

use InvalidArgumentException;
use League\CommonMark\Extension\CommonMark\Node\Block\FencedCode;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Extension\CommonMark\Renderer\Block\FencedCodeRenderer as BaseFencedCodeRenderer;
use Tempest\Highlight\Highlighter;
use League\CommonMark\Renderer\NodeRendererInterface;

class HighlightCodeBlockRenderer implements NodeRendererInterface
{
    public function render(Node $node, ChildNodeRendererInterface $childRenderer)
    {
        if (! $node instanceof FencedCode) {
            throw new InvalidArgumentException('Block must be instance of ' . FencedCode::class);
        }

        $renderer = new BaseFencedCodeRenderer();

        $language = $node->getInfoWords()[0] ?? 'txt';

        $highlight = new Highlighter();

        /** @var \League\CommonMark\Util\HtmlElement $codeBlock */
        $codeBlock = $renderer->render($node, $childRenderer);

        /** @var string $codeText */
        $codeText = $codeBlock->getContents(false)->getContents();

        // Remove hljs tags
        $codeText = preg_replace(['/&lt;hljs(.*?)*&gt;/', '/&lt;\/hljs&gt;/'], '', $codeText);

        $codeBlock->setContents($highlight->parse($codeText, $language));

        $codeBlock->setContents($codeBlock->getContents());

        return $codeBlock;
    }
}
