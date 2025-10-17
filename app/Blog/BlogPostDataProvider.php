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
        foreach ($this->repository->all() as $post) {
            yield [
                'slug' => $post->slug,
            ];
        }
    }
}