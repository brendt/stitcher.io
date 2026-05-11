<?php

namespace App\Php\Search;

use Tempest\Http\Request;
use Tempest\Router\Get;
use Tempest\Router\Post;
use Tempest\View\View;
use function Tempest\View\view;

final readonly class SearchController
{
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

        return view(
            'x-php-search-results.view.php',
            keyword: $keyword,
            matches: $matches,
        );
    }
}