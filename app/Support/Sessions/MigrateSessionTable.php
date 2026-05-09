<?php

namespace App\Support\Sessions;

use Tempest\Database\MigratesUp;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CompoundStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;
use Tempest\Database\QueryStatements\DropTableStatement;

final class MigrateSessionTable implements MigratesUp
{
    public string $name = '2026-01-08_migrate_sessions_table';

    public function up(): QueryStatement
    {
        return new CompoundStatement(
            new DropTableStatement('sessions'),
            new CreateTableStatement('sessions')
                ->uuid('id')
                ->datetime('created_at')
                ->datetime('last_active_at')
                ->text('data'),
        );
    }
}