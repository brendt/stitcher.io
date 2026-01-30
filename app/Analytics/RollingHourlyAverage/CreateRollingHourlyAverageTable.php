<?php

namespace App\Analytics\RollingHourlyAverage;

use Tempest\Database\MigratesUp;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;

final class CreateRollingHourlyAverageTable implements MigratesUp
{
    public string $name = '2026-01-30_01_create_rolling_hourly_average_table';

    public function up(): QueryStatement
    {
        return CreateTableStatement::forModel(RollingHourlyAverage::class)
            ->primary()
            ->datetime('date')
            ->integer('count')
            ->unique('date');
    }
}