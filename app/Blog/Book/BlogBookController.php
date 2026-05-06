<?php

namespace App\Blog\Book;

use Tempest\Http\Request;
use Tempest\Router\Get;
use Tempest\View\View;
use function Tempest\View\view;

final class BlogBookController
{
    #[Get('/blog/book')]
    public function index(BlogBookRepository $repository, Request $request): View
    {
        $chapters = $repository->all($request->get('filter'), $request->get('collection'));

        $toc = [];
        $currentTocPage = 0;

        foreach ($chapters as $chapter) {
            $toc[$currentTocPage] ??= [];
            $toc[$currentTocPage][] = $chapter;

            if (count($toc[$currentTocPage]) >= 34) {
                $currentTocPage++;
            }
        }

        $pageOffset = -1 * count($toc) - 2;

        return view(
            'blog-book.view.php',
            chapters: $chapters,
            title: 'pdf-stitcher-book',
            wordCount: $repository->wordCount(),
            toc: $toc,
            pageOffset: $pageOffset,
        );
    }
}