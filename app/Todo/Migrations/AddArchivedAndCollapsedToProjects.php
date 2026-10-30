<?php

namespace App\Todo\Migrations;

use Tempest\Database\MigratesUp;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\AlterTableStatement;
use Tempest\Database\QueryStatements\BooleanStatement;
use Tempest\Database\QueryStatements\DatetimeStatement;

final class AddArchivedAndCollapsedToProjects implements MigratesUp
{
    public string $name = '2026-10-30-004_add_archived_and_collapsed_to_projects';

    public function up(): QueryStatement
    {
        return new AlterTableStatement('projects')
            ->add(new DatetimeStatement('archivedAt', nullable: true))
            ->add(new BooleanStatement('collapsed', default: false));
    }
}
