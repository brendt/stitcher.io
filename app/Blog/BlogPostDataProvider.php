<?php

namespace App\Blog;

use Generator;
use Tempest\Router\DataProvider;
use function Tempest\Support\arr;

final readonly class BlogPostDataProvider implements DataProvider
{
    public function __construct(
        private BlogPostRepository $repository,
    ) {}

    public function provide(): Generator
    {
        $paths = arr(glob(__DIR__ . "/Content/*.md"))->reverse();

        foreach ($paths as $path) {
            preg_match('/\d+-\d+-\d+-(?<slug>.*)\.md/', $path, $matches);

            yield [
                'slug' => $matches['slug'],
            ];
        }
    }
}