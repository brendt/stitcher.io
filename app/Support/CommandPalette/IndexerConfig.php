<?php

namespace App\Support\CommandPalette;

final class IndexerConfig
{
    public function __construct(
        public array $indexerClasses = [],
    ) {}
}
