<?php

declare(strict_types=1);

namespace App\Analytics\VisitsPerMonth;

use Tempest\Database\MigratesUp;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;

final class CreateVisitsPerMonthTable implements MigratesUp
{
    public string $name = '2026-01-16_01_create_visits_per_month_table';

    public function up(): QueryStatement
    {
        return CreateTableStatement::forModel('visits_per_month')
            ->primary()
            ->datetime('date')
            ->integer('count')
            ->unique('date');
    }
}