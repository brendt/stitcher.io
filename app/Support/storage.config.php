<?php

use Tempest\Storage\Config\LocalStorageConfig;

return new LocalStorageConfig(
    path: __DIR__ . '/../../.tempest/storage/',
    tag: 'local',
);