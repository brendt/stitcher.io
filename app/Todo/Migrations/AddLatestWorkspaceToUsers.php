<?php

namespace App\Todo\Migrations;

use Tempest\Database\MigratesUp;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\AlterTableStatement;
use Tempest\Database\QueryStatements\IntegerStatement;

final class AddLatestWorkspaceToUsers implements MigratesUp
{
    public string $name = '2026-10-30-005_add_latest_workspace_to_users';

    public function up(): QueryStatement
    {
        return new AlterTableStatement('users')
            ->add(new IntegerStatement('latestWorkspaceId', unsigned: true, nullable: true));
    }
}
