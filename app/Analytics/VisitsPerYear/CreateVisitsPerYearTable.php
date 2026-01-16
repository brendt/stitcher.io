<?php

declare(strict_types=1);

namespace App\Analytics\VisitsPerYear;

use Tempest\Database\MigratesUp;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;

final class CreateVisitsPerYearTable implements MigratesUp
{
    public string $name = '2026-01-16_01_create_visits_per_year_table';

    public function up(): QueryStatement
    {
        return CreateTableStatement::forModel(VisitsPerYear::class)
            ->primary()
            ->datetime('date')
            ->integer('count')
            ->unique('date');
    }
}