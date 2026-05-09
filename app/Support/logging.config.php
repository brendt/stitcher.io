<?php

use Tempest\Log\Config\SimpleLogConfig;
use Tempest\Log\LogLevel;

return new SimpleLogConfig(
    path: __DIR__ . '/../../log/tempest.log',
    minimumLogLevel: LogLevel::ERROR,
);