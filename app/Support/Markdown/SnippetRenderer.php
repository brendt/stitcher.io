<?php

namespace App\Support\Markdown;

use InvalidArgumentException;
use League\CommonMark\Node\Block\Paragraph;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\Block\ParagraphRenderer;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Util\HtmlElement;
use Tempest\View\Exceptions\ViewNotFound;
use Tempest\View\Renderers\TempestViewRenderer;
use function Tempest\Support\str;

final readonly class SnippetRenderer implements NodeRendererInterface
{
    public function __construct(
        private TempestViewRenderer $renderer,
    ) {}

    public function render(Node $node, ChildNodeRendererInterface $childRenderer): string|HtmlElement
    {
        if (! $node instanceof Paragraph) {
            throw new InvalidArgumentException('Block must be instance of ' . Paragraph::class);
        }

        $children = $node->children();

        if (count($children) !== 1) {
            return new ParagraphRenderer()->render($node, $childRenderer);
        }

        $content = str($childRenderer->renderNodes($children));

        if (! $content->startsWith('{{ ') || ! $content->endsWith(' }}')) {
            return new ParagraphRenderer()->render($node, $childRenderer);
        }

        $snippet = $content->between('{{ ', ' }}')->replace(':', '_');

        try {
            return $this->renderer->render(__DIR__ . "/../../Blog/Snippets/{$snippet}.view.php");
        } catch (ViewNotFound) {
            return '';
        }
    }
}