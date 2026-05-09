<?php

declare(strict_types=1);

namespace App\Analytics\VisitsPerMinute;

use Tempest\Database\MigratesUp;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;

final class CreateVisitsPerMinuteTable implements MigratesUp
{
    public string $name = '2026-01-16_01_create_visits_per_minute_table';

    public function up(): QueryStatement
    {
        return CreateTableStatement::forModel(VisitsPerMinute::class)
            ->primary()
            ->datetime('time')
            ->integer('count')
            ->unique('time');
    }
}