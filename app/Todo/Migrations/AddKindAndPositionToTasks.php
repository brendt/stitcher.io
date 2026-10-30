<?php

namespace App\Todo\Migrations;

use App\Todo\TaskKind;
use Tempest\Database\MigratesUp;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\AlterTableStatement;
use Tempest\Database\QueryStatements\EnumStatement;
use Tempest\Database\QueryStatements\IntegerStatement;

final class AddKindAndPositionToTasks implements MigratesUp
{
    public string $name = '2026-10-30-006_add_kind_and_position_to_tasks';

    public function up(): QueryStatement
    {
        return new AlterTableStatement('tasks')
            ->add(new EnumStatement('kind', TaskKind::class, default: TaskKind::TASK))
            ->add(new IntegerStatement('position', unsigned: true, default: 0));
    }
}
