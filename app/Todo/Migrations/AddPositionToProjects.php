<?php

namespace App\Todo\Migrations;

use Tempest\Database\MigratesUp;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\AlterTableStatement;
use Tempest\Database\QueryStatements\IntegerStatement;

final class AddPositionToProjects implements MigratesUp
{
    public string $name = '2026-10-30-007_add_position_to_projects';

    public function up(): QueryStatement
    {
        return new AlterTableStatement('projects')
            ->add(new IntegerStatement('position', unsigned: true, default: 0));
    }
}
