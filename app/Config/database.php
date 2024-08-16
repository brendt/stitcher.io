<?php

use Tempest\Database\DatabaseConfig;
use Tempest\Database\Drivers\SQLiteDriver;

return new DatabaseConfig(
    driver: new SQLiteDriver(__DIR__ . '/../database.sqlite'),
);