<?php

namespace App\Support\CommandPalette;

use Tempest\Console\ConsoleCommand;
use Tempest\Console\HasConsole;
use Tempest\Container\Container;

final readonly class IndexCommandPaletteCommand
{
    use HasConsole;

    public function __construct(
        private IndexerConfig $indexerConfig,
        private Container $container,
    ) {}

    #[ConsoleCommand(
        name: 'command-palette:index',
        description: 'Exports available commands to a JSON index file that can be consumed by the front-end.',
        aliases: ['index'],
    )]
    public function __invoke(): void
    {
        $this->console->header('Indexing…');

        $index = [];

        foreach ($this->indexerConfig->indexerClasses as $indexerClass) {
            /** @var Indexer $indexer */
            $indexer = $this->container->get($indexerClass);
            $index = [...$index, ...$indexer->index()];

            $this->keyValue($indexerClass, "<style='fg-green'>DONE</style>");
        }

        file_put_contents(
            __DIR__ . '/index.json',
            json_encode($index),
        );

        $this->keyValue('Saving index', "<style='fg-green'>DONE</style>");
    }
}
