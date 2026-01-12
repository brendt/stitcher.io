<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Tempest\Upgrade\Set\TempestSetList;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/app',
        __DIR__ . '/tests',
    ])
    ->withSets([TempestSetList::TEMPEST_30]);
