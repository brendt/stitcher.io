<?php

namespace App\Analytics\RollingDailyAverage;

use Tempest\Database\MigratesUp;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;

final class CreateRollingDailyAverageTable implements MigratesUp
{
    public string $name = '2026-01-30_01_create_rolling_daily_average_table';

    public function up(): QueryStatement
    {
        return CreateTableStatement::forModel(RollingDailyAverage::class)
            ->primary()
            ->datetime('date')
            ->integer('count')
            ->unique('date');
    }
}