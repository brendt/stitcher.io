<?php

namespace App;

use App\Comments\Comment;
use App\Auth\User;
use DateTimeImmutable;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\HasConsole;

final readonly class SeedCommand
{
    use HasConsole;

    #[ConsoleCommand('seed')]
    public function __invoke(): void
    {
        $user = (new User('Brent', 'brendt@stitcher.io'))->save();

        $data = json_decode(file_get_contents(__DIR__ . '/comments.json'), true)['data'];

        foreach ($data as $item) {
            (new Comment(
                user: $user,
                postId: $item['postId'],
                comment: $item['comment'],
                createdAt: new DateTimeImmutable($item['createdAt']),
            ))->save();
        }

        $this->success('Seeding done');
    }
}