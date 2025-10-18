<?php

namespace App\Support\Markdown;

use Brendt\Stitcher\Plugin\Highlight\TempestConsoleWebLanguage;
use Brendt\Stitcher\Plugin\Highlight\TempestViewLanguage;
use InvalidArgumentException;
use League\CommonMark\Extension\CommonMark\Node\Block\FencedCode;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Util\HtmlElement;
use Tempest\Highlight\Highlighter;
use League\CommonMark\Renderer\NodeRendererInterface;

final readonly class HighlightCodeBlockRenderer implements NodeRendererInterface
{
    public function __construct(
        private Highlighter $highlighter,
    ) {}

    public function render(Node $node, ChildNodeRendererInterface $childRenderer): HtmlElement
    {
        if (! $node instanceof FencedCode) {
            throw new InvalidArgumentException('Block must be instance of ' . FencedCode::class);
        }

        $code = $node->getLiteral();
        $code = preg_replace(['/<hljs(.*?)*>/', '/<\/hljs>/'], '', $code);
        $language = $node->getInfoWords()[0] ?? 'txt';

        return new HtmlElement(
            'pre',
            [],
            $this->highlighter->parse($code, $language)
        );
    }
}
