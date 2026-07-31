<?php

use Tempest\KeyValue\Redis\Config\RedisConfig;

use function Tempest\env;

return new RedisConfig(
    host: env('TEST_REDIS_HOST', default: '127.0.0.1'),
    port: (int) env('TEST_REDIS_PORT', default: 6379),
);
