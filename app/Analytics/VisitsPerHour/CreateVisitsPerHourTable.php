<?php

declare(strict_types=1);

namespace App\Analytics\VisitsPerHour;

use Tempest\Database\MigratesUp;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;

final class CreateVisitsPerHourTable implements MigratesUp
{
    public string $name = '2026-01-16_01_create_visits_per_hour_table';

    public function up(): QueryStatement
    {
        return CreateTableStatement::forModel(VisitsPerHour::class)
            ->primary()
            ->datetime('hour')
            ->integer('count')
            ->unique('hour');
    }
}