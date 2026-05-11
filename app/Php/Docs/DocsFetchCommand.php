<?php

namespace App\Php\Docs;

use Tempest\Console\ConsoleCommand;
use Tempest\Console\HasConsole;
use function Tempest\Support\Filesystem\delete;

final class DocsFetchCommand
{
    use HasConsole;

    #[ConsoleCommand(name: 'php:fetch', aliases: ['docs:fetch'])]
    public function __invoke(): void
    {
        $path = __DIR__ . '/xml';

        if (is_dir($path)) {
            delete($path);
        }

        passthru("git clone git@github.com:php/doc-en.git --depth 1 --single-branch {$path}");

        $this->success('Done');
    }
}