<?php

declare(strict_types=1);

namespace App\Analytics\VisitsPerPostPerWeek;

use Tempest\Database\MigratesUp;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;

final class CreateVisitsPerPostPerWeekTable implements MigratesUp
{
    public string $name = '2026-01-16_01_create_visits_per_post_per_week_table';

    public function up(): QueryStatement
    {
        return CreateTableStatement::forModel(VisitsPerPostPerWeek::class)
            ->primary()
            ->string('uri')
            ->datetime('date')
            ->integer('count')
            ->unique('uri', 'date');
    }
}