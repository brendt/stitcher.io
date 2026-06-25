<?php

namespace App\Time;

use Tempest\Database\MigratesUp;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;

final class CreateTimeTable implements MigratesUp
{
    public string $name = '2026-06-22_create_time_table';

    public function up(): QueryStatement
    {
        return new CreateTableStatement('time_entries')
            ->primary()
            ->datetime('start')
            ->datetime('end', nullable: true);
    }
}
