<?php

namespace App\Todo\Migrations;

use Tempest\Database\MigratesUp;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;
use Tempest\Database\QueryStatements\OnDelete;

final class CreateProjectsTable implements MigratesUp
{
    public string $name = '2026-10-30-002_create_projects_table';

    public function up(): QueryStatement
    {
        return new CreateTableStatement('projects')
            ->primary()
            ->belongsTo('projects.workspace_id', 'workspaces.id', onDelete: OnDelete::CASCADE)
            ->string('name')
            ->datetime('completedAt', nullable: true);
    }
}
