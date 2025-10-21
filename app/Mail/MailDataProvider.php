<?php

namespace App\Mail;

use Generator;
use Tempest\Router\DataProvider;

final readonly class MailDataProvider implements DataProvider
{
    public function __construct(
        private MailRepository $repository,
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