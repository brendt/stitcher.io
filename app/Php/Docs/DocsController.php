<?php

namespace App\Php\Docs;

use App\Php\Search\Index;
use App\Php\Support\Breadcrumbs;
use Tempest\Http\Responses\Redirect;
use Tempest\Router\Get;
use Tempest\View\View;
use function Tempest\Router\uri;
use function Tempest\Support\arr;
use function Tempest\View\view;

final class DocsController
{
    #[Get('/php/docs')]
    public function index(): View
    {
        return view('php-docs-index.view.php');
    }

    #[Get('/php/docs/{slug:.*}')]
    public function show(string $slug): View|Redirect
    {
        if (is_dir(__DIR__ . '/md/' . $slug)) {
            return $this->directory($slug);
        } elseif (is_file(__DIR__ . '/md/' . $slug . '.md')) {
            return $this->file($slug);
        }

        return new Redirect('/php/docs');
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
            base: '/php/docs/',
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

        return view(
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
            base: '/php/docs',
        );

        $related = arr(glob(pathinfo($path, PATHINFO_DIRNAME) . '/*'))
            ->mapWithKeys(function (string $path) {
                $slug = str_replace([__DIR__ . '/md/', '.md'], '', $path);

                $slug = ltrim($slug, '/');

                yield uri([self::class, 'show'], slug: $slug) => pathinfo($slug, PATHINFO_FILENAME);
            });

        $originalUri = 'https://php.net/manual/en/' . str_replace('/', '.', ltrim($slug, '/')) . '.php';

        $index = Index::select()->where('uri', $slug)->first();

        return view(
            'php-docs-show.view.php',
            content: $markdown,
            breadcrumbs: $breadcrumbs,
            related: $related,
            originalUri: $originalUri,
            index: $index,
        );
    }
}