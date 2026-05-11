<?php

namespace App\Php\Docs\Indexers;

use App\Php\Docs\DocsController;
use App\Php\Search\Index;
use App\Php\Search\Indexer;
use function Tempest\Router\uri;
use function Tempest\Support\str;

final class MarkdownDocsIndexer implements Indexer
{
    public function index(): void
    {
        $base = __DIR__ . '/../md/';

        $files = glob($base . '{,*/,*/*/,*/*/*/}*.md', GLOB_BRACE);

        foreach ($files as $path) {
            $slug = str_replace([$base, '.md'], '', $path);

            $content = file_get_contents($path);

            $title = str($content)->afterFirst('<h1>')->before('</h1>')->before(PHP_EOL)->trim()->toString();

            Index::updateOrCreate(
                [
                    'uri' => ltrim($slug, '/')
                            |> (fn ($x) => uri([DocsController::class, 'show'], slug: $x))
                            |> (fn ($x) => parse_url($x, PHP_URL_PATH))
                ],
                [
                    'priority' => 1,
                    'title' => $title,
                ],
            );
        }
    }
}