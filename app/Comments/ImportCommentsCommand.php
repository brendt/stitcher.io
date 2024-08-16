<?php

namespace App\Comments;

use Tempest\Console\ConsoleCommand;
use Tempest\Console\HasConsole;

final readonly class ImportCommentsCommand
{
    use HasConsole;

    #[ConsoleCommand('import:comments')]
    public function __invoke(): void
    {
        $data = json_decode(file_get_contents(__DIR__ . '/comments.json'), true)['data'];

        foreach ($data as $item) {
            Comment::updateOrCreate(
                [
                    'postId' => $item['postId'],
                    'email' => $item['email'],
                    'comment' => $item['comment'],
                    'createdAt' => $item['createdAt'],
                    'ip' => $item['ip'],
                ],
                []
            );
        }
    }
}