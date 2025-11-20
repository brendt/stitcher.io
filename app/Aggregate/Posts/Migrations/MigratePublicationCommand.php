<?php

namespace App\Aggregate\Posts\Migrations;

use App\Aggregate\Posts\Post;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\HasConsole;

final class MigratePublicationCommand
{
    use HasConsole;

    #[ConsoleCommand]
    public function __invoke(): void
    {
        Post::published()
            ->whereNull('publicationDate')
            ->chunk(function (array $posts) {
                foreach ($posts as $post) {
                    $post->update(
                        publicationDate: $post->createdAt,
                    );

                    $this->success($post->uri);
                }
            });

        $this->info('Done!');
    }
}