<?php

namespace App\Todo\Migrations;

use Tempest\Database\MigratesUp;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;
use Tempest\Database\QueryStatements\OnDelete;

final class CreateTasksTable implements MigratesUp
{
    public string $name = '2026-10-30-003_create_tasks_table';

    public function up(): QueryStatement
    {
        return new CreateTableStatement('tasks')
            ->primary()
            ->belongsTo('tasks.project_id', 'projects.id', onDelete: OnDelete::CASCADE)
            ->string('name')
            ->datetime('completedAt', nullable: true);
    }
}
