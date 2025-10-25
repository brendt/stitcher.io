<?php

namespace App\PhpDocs;

use Tempest\Http\Request;
use Tempest\Http\Responses\Redirect;
use Tempest\Router\Get;
use Tempest\Router\Post;
use Tempest\View\View;
use function Tempest\Router\uri;
use function Tempest\Support\arr;
use function Tempest\Support\path;

final class PhpDocsController
{
    #[Get('/php')]
    public function index(): View
    {
        return $this->directory('');
    }

    #[Get('/php/{slug:.*}')]
    public function show(string $slug): View|Redirect
    {
        if (is_dir(__DIR__ . '/md/' . $slug)) {
            return $this->directory($slug);
        } elseif (is_file(__DIR__ . '/md/' . $slug . '.md')) {
            return $this->file($slug);
        }

        return new Redirect('/php');
    }

    private function directory(string $slug): View
    {
        $files = arr(glob(__DIR__ . '/md/' . $slug . '/*'))
            ->mapWithKeys(function (string $path) {
                $slug = str_replace([__DIR__ . '/md/', '.md'], '', $path);

                $slug = ltrim($slug, '/');

                yield uri([self::class, 'show'], slug: $slug) => $slug;
            });

        $breadcrumbs = new Breadcrumbs(
            path: $slug,
            base: '/php/',
        );

//        $keyword = 'array';
//        $matches = Index::select()
//            ->where(
//                'title LIKE ? OR uri LIKE ?',
//                "%{$keyword}%",
//                "%{$keyword}%",
//            )
//            ->where('title <> ""')
//            ->paginate(10)->data;

        return \Tempest\view(
            'php-docs-directory.view.php',
            files: $files,
            breadcrumbs: $breadcrumbs,
            matches: $matches ?? [],
            keyword: $keyword ?? null,
        );
    }

    private function file(string $slug): View
    {
        $path = __DIR__ . '/md/' . $slug . '.md';

        $markdown = file_get_contents($path);

        $breadcrumbs = new Breadcrumbs(
            path: $slug,
            base: '/php/',
        );

        $related = arr(glob(pathinfo($path, PATHINFO_DIRNAME) . '/*'))
            ->mapWithKeys(function (string $path) {
                $slug = str_replace([__DIR__ . '/md/', '.md'], '', $path);

                $slug = ltrim($slug, '/');

                yield uri([self::class, 'show'], slug: $slug) => pathinfo($slug, PATHINFO_FILENAME);
            });

        return \Tempest\view(
            'php-docs-show.view.php',
            content: $markdown,
            breadcrumbs: $breadcrumbs,
            related: $related,
        );
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
}