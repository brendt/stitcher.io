<?php

namespace App\Todo\Migrations;

use Tempest\Database\MigratesUp;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\AlterTableStatement;
use Tempest\Database\QueryStatements\BelongsToStatement;
use Tempest\Database\QueryStatements\CompoundStatement;
use Tempest\Database\QueryStatements\IntegerStatement;
use Tempest\Database\QueryStatements\OnDelete;
use Tempest\Database\QueryStatements\RawStatement;

final class MoveWorkspacesToProfiles implements MigratesUp
{
    public string $name = '2026-11-02-002_move_workspaces_to_profiles';

    public function up(): QueryStatement
    {
        // Workspaces belong to a profile instead of a user, so that every user of that profile shares them
        return new CompoundStatement(
            new AlterTableStatement('workspaces')
                ->add(new IntegerStatement('profile_id', nullable: true)),
            new RawStatement('UPDATE workspaces INNER JOIN users ON users.id = workspaces.user_id SET workspaces.profile_id = users.profile_id'),
            new AlterTableStatement('workspaces')
                ->dropConstraint('fk_users_workspaces_user_id')
                ->dropColumn('user_id')
                ->modify(new IntegerStatement('profile_id'))
                ->add(new BelongsToStatement('workspaces.profile_id', 'profiles.id', onDelete: OnDelete::CASCADE)),
        );
    }
}
