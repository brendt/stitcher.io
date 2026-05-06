<?php

namespace App\Support\Markdown;

use InvalidArgumentException;
use League\CommonMark\Extension\CommonMark\Node\Block\FencedCode;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Util\ArrayCollection;
use League\CommonMark\Util\HtmlElement;
use Tempest\Highlight\Highlighter;

final readonly class BookFencedCodeRenderer implements NodeRendererInterface
{
    private const int MAX_LINES = 10;

    public function __construct(
        private Highlighter $highlighter,
    ) {}

    public function render(Node $node, ChildNodeRendererInterface $childRenderer): string|HtmlElement|ArrayCollection
    {
        if (! $node instanceof FencedCode) {
            throw new InvalidArgumentException('Block must be instance of ' . FencedCode::class);
        }

        $code = $node->getLiteral();
        $code = preg_replace(['/<hljs(.*?)*>/', '/<\/hljs>/'], '', $code);
        $language = $node->getInfoWords()[0] ?? 'txt';

        $lines = explode(PHP_EOL, trim($code));

        $codeBlocks = [];

        while ($lines !== []) {
            $snippet = implode(PHP_EOL, array_splice($lines, 0, self::MAX_LINES));

            $codeBlocks[] = new HtmlElement(
                'pre',
                [],
                $this->highlighter->parse($snippet, $language),
            );
        }

        return array_map(fn (HtmlElement $element) => trim((string)$element), $codeBlocks)
                |> array_filter(...)
                |> (fn ($x) => implode(PHP_EOL, $x));
    }
}