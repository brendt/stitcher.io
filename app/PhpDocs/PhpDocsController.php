<?php

namespace App\PhpDocs;

use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\Http\Responses\NotFound;
use Tempest\Router\Get;
use Tempest\Router\Post;
use Tempest\View\View;

final class PhpDocsController
{
    #[Get('/php')]
    public function index(): View
    {
        $categories = glob(__DIR__ . '/md/*', GLOB_ONLYDIR);

//        ld($categories);

        $keyword = 'array';
        $matches = Index::select()
            ->where(
                'title LIKE ? OR uri LIKE ?',
                "%{$keyword}%",
                "%{$keyword}%",
            )
            ->where('title <> ""')
            ->paginate(10)->data;

        return \Tempest\view('php-docs-index.view.php', matches: $matches, keyword: $keyword);
    }

    #[Get('/php/search')]
    #[Post('/php/search')]
    public function search(Request $request): View
    {
        $keyword = $request->get('search');

        $matches = [];

        if ($keyword) {
            $matches = Index::select()
                ->where(
                    'title LIKE ? OR uri LIKE ?',
                    "%{$keyword}%",
                    "%{$keyword}%",
                )
                ->where('title <> ""')
                ->paginate(10)->data;
        }

        return \Tempest\view(
            'x-php-search-results.view.php',
            keyword: $keyword,
            matches: $matches,
        );
    }

    #[Get('/php/{slug:.*}')]
    public function show(string $slug): View|NotFound
    {
        $markdown = @file_get_contents(__DIR__ . '/md/' . $slug . '.md');

        if (! $markdown) {
            return new NotFound();
        }

        return \Tempest\view('php-docs-show.view.php', content: $markdown);
    }
}