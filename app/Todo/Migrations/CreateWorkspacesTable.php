<?php

namespace App\Todo\Migrations;

use Tempest\Database\MigratesUp;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;
use Tempest\Database\QueryStatements\OnDelete;

final class CreateWorkspacesTable implements MigratesUp
{
    public string $name = '2026-10-30-001_create_workspaces_table';

    public function up(): QueryStatement
    {
        return new CreateTableStatement('workspaces')
            ->primary()
            ->belongsTo('workspaces.user_id', 'users.id', onDelete: OnDelete::CASCADE)
            ->string('name');
    }
}
