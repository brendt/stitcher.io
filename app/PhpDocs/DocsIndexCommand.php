<?php

namespace App\PhpDocs;

use App\Support\CommandPalette\Command;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\HasConsole;

final class DocsIndexCommand
{
    use HasConsole;

    public function __construct(
        private PhpDocsIndexer $indexer,
    ) {}

    #[ConsoleCommand]
    public function __invoke(): void
    {
        $this->indexer->index()->each(function (Command $command) {
            Index::updateOrCreate(
                ['title' => $command->title],
                ['uri' => $command->uri],
            );

            $this->success($command->uri);
        });

        $this->success('Done');
    }
}