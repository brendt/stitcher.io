<?php

namespace App\Todo\Migrations;

use Tempest\Database\MigratesUp;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\AlterTableStatement;
use Tempest\Database\QueryStatements\CompoundStatement;
use Tempest\Database\QueryStatements\IntegerStatement;
use Tempest\Database\QueryStatements\RawStatement;

final class MoveLatestWorkspaceToProfiles implements MigratesUp
{
    public string $name = '2026-11-02-003_move_latest_workspace_to_profiles';

    public function up(): QueryStatement
    {
        // The workspace that was opened last is shared by every user of a profile, just like the workspaces themselves
        return new CompoundStatement(
            new AlterTableStatement('profiles')
                ->add(new IntegerStatement('latestWorkspaceId', unsigned: true, nullable: true)),
            new RawStatement(
                'UPDATE profiles INNER JOIN users ON users.profile_id = profiles.id SET profiles.latestWorkspaceId = users.latestWorkspaceId WHERE users.latestWorkspaceId IS NOT NULL',
            ),
            new AlterTableStatement('users')
                ->dropColumn('latestWorkspaceId'),
        );
    }
}
