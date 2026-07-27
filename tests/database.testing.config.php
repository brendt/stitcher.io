<?php

use Tempest\Database\Config\MysqlConfig;

use function Tempest\env;

return new MysqlConfig(
    host: env('TEST_DATABASE_HOST', default: 'localhost'),
    port: env('TEST_DATABASE_PORT', default: '3306'),
    username: env('TEST_DATABASE_USERNAME', default: 'root'),
    password: env('TEST_DATABASE_PASSWORD', default: ''),
    database: env('TEST_DATABASE_DATABASE', default: 'stitcher-testing'),
);
