<?php

namespace App\PhpDocs;

use Tempest\Http\Response;
use Tempest\Http\Responses\NotFound;
use Tempest\Router\Get;
use Tempest\View\View;

final class PhpDocsController
{
    #[Get('/php')]
    public function index(): View
    {
        $categories = glob(__DIR__ . '/md/*', GLOB_ONLYDIR);

//        ld($categories);

        return \Tempest\view('php-docs-index.view.php');
    }

    #[Get('/php/search/{keyword}')]
    public function search(string $keyword): Response
    {
        $matches = Index::select()->where('title LIKE ?', "%{$keyword}%")->paginate();

        ld($matches);
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