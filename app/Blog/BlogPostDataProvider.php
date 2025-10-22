<?php

namespace App\Blog;

use Generator;
use Tempest\Router\DataProvider;

final readonly class BlogPostDataProvider implements DataProvider
{
    public function __construct(
        private BlogPostRepository $repository,
    ) {}

    public function provide(): Generator
    {
        foreach (glob(__DIR__ . "/Content/*.md") as $path) {
            preg_match('/\d+-\d+-\d+-(?<slug>.*)\.md/', $path, $matches);
            ld($matches);
            yield [
                'slug' => $matches['slug'],
            ];
        }
    }
}