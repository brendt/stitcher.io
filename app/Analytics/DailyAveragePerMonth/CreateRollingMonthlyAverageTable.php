<?php

namespace App\Analytics\DailyAveragePerMonth;

use Tempest\Database\MigratesUp;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;

final class CreateRollingMonthlyAverageTable implements MigratesUp
{
    public string $name = '2026-01-30_01_create_rolling_monthly_average_table';

    public function up(): QueryStatement
    {
        return CreateTableStatement::forModel(RollingMonthlyAverage::class)
            ->primary()
            ->datetime('date')
            ->integer('count')
            ->unique('date');
    }
}