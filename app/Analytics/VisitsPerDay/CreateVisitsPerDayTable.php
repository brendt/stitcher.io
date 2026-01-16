<?php

declare(strict_types=1);

namespace App\Analytics\VisitsPerDay;

use Tempest\Database\MigratesUp;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;

final class CreateVisitsPerDayTable implements MigratesUp
{
    public string $name = '2026-01-16_01_create_visits_per_day_table';

    public function up(): QueryStatement
    {
        return CreateTableStatement::forModel('visits_per_day')
            ->primary()
            ->datetime('date')
            ->integer('count')
            ->unique('date');
    }
}