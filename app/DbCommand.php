<?php

namespace App;

use Tempest\Console\ConsoleCommand;
use Tempest\Database\Config\DatabaseConfig;

final class DbCommand
{
    public function __construct(
        private DatabaseConfig $config
    ) {}

    #[ConsoleCommand]
    public function __invoke(): void
    {
        ld($this->config);
    }
}