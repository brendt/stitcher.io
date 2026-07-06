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
        $snippet = trim($this->snippet);

        if (str_starts_with($snippet, 'yt:')) {
            $id = str_replace('yt:', '', $snippet);

            return <<<HTML
            <div class="youtube-embed">
            <iframe width="500" height="280" src="https://www.youtube.com/embed/$id" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
            </div>
            HTML;
        }

        $snippetFile = str_replace([' ', ':'], ['', '_'], $this->snippet);

        $viewRenderer = get(ViewRenderer::class);

        try {
            return $viewRenderer->render(str_replace(['///', '//'], '/', "{$this->root}/{$snippetFile}.view.php"));
        } catch (ViewNotFound) {
            return '';
        }
    }
}
