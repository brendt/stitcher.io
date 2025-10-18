<?php

namespace App\Support\Markdown;

use InvalidArgumentException;
use League\CommonMark\Extension\CommonMark\Node\Inline\Code;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use Tempest\Highlight\Highlighter;

final readonly class HighlightInlineCodeRenderer implements NodeRendererInterface
{
    public function __construct(
        private Highlighter $highlighter,
    ) {}

    public function render(Node $node, ChildNodeRendererInterface $childRenderer): string
    {
        if (! $node instanceof Code) {
            throw new InvalidArgumentException('Block must be instance of ' . Code::class);
        }

        preg_match('/^\{(?<match>[\w]+)\}(?<code>.*)/', $node->getLiteral(), $match);

        $language = $match['match'] ?? 'txt';
        $code = $match['code'] ?? $node->getLiteral();
        $code = preg_replace(['/<hljs(.*?)*>/', '/<\/hljs>/'], '', $code);

        return '<code>' . $this->highlighter->parse($code, $language) . '</code>';
    }
}
