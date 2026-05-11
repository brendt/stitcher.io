<?php

namespace App\Php\Search;

use Tempest\Container\Singleton;

#[Singleton]
final class IndexConfig
{
    public function __construct(
        /** @var array<array-key, class-string<Indexer>> */
        public array $indexers = [],
    ) {}
}