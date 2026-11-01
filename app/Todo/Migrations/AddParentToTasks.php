<?php

namespace App\Todo\Migrations;

use Tempest\Database\MigratesUp;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\AlterTableStatement;
use Tempest\Database\QueryStatements\BelongsToStatement;
use Tempest\Database\QueryStatements\IntegerStatement;
use Tempest\Database\QueryStatements\OnDelete;

final class AddParentToTasks implements MigratesUp
{
    public string $name = '2026-11-01-001_add_parent_to_tasks';

    public function up(): QueryStatement
    {
        // Subtasks of a deleted task are kept, they're no longer a subtask then
        return new AlterTableStatement('tasks')
            ->add(new IntegerStatement('parent_id', nullable: true))
            ->add(new BelongsToStatement('tasks.parent_id', 'tasks.id', onDelete: OnDelete::SET_NULL));
    }
}
