<?php

namespace App\Support\Markdown;

use Tempest\Markdown\Parser;
use Tempest\Markdown\Token;
use Tempest\View\Exceptions\ViewNotFound;
use Tempest\View\ViewRenderer;

use function Tempest\Container\get;

final readonly class SnippetToken implements Token
{
    public function __construct(
        public string $root,
        public string $snippet,
    ) {}

    public function parse(Parser $parser): string
    {
        $snippetFile = str_replace([' ', ':'], ['', '_'], $this->snippet);

        $viewRenderer = get(ViewRenderer::class);

        try {
            return $viewRenderer->render(str_replace(['///', '//'], '/', "{$this->root}/{$snippetFile}.view.php"));
        } catch (ViewNotFound) {
            return '';
        }
    }
}
