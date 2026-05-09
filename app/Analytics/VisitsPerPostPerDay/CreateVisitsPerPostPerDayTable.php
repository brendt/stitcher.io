<?php

declare(strict_types=1);

namespace App\Analytics\VisitsPerPostPerDay;

use Tempest\Database\MigratesUp;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;

final class CreateVisitsPerPostPerDayTable implements MigratesUp
{
    public string $name = '2026-01-16_01_create_visits_per_post_per_day_table';

    public function up(): QueryStatement
    {
        return CreateTableStatement::forModel(VisitsPerPostPerDay::class)
            ->primary()
            ->string('uri')
            ->datetime('date')
            ->integer('count')
            ->unique('uri', 'date');
    }
}