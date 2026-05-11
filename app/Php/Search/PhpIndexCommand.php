<?php

namespace App\Php\Search;

use Tempest\Console\ConsoleCommand;
use Tempest\Console\HasConsole;
use Tempest\Container\Container;
use function Tempest\Database\query;

final class PhpIndexCommand
{
    use HasConsole;

    public function __construct(
        private readonly IndexConfig $config,
        private readonly Container $container,
    ) {}

    #[ConsoleCommand('php:index', aliases: ['docs:index'])]
    public function __invoke(bool $clean = false): void
    {
        if ($clean) {
            query(Index::class)->delete()->allowAll()->execute();

            $this->error('Index dropped');
        }

        foreach ($this->config->indexers as $indexerClass) {
            $this->info($indexerClass);

            /** @var \App\Php\Search\Indexer $indexer */
            $indexer = $this->container->get($indexerClass);

            $indexer->index();
        }

        $this->console->success('Done');
    }
}