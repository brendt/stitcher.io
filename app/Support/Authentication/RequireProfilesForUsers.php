<?php

namespace App\Support\Authentication;

use Tempest\Database\MigratesUp;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\AlterTableStatement;
use Tempest\Database\QueryStatements\BelongsToStatement;
use Tempest\Database\QueryStatements\CompoundStatement;
use Tempest\Database\QueryStatements\IntegerStatement;
use Tempest\Database\QueryStatements\OnDelete;
use Tempest\Database\QueryStatements\RawStatement;

final class RequireProfilesForUsers implements MigratesUp
{
    public string $name = '2026-11-02-004_require_profiles_for_users';

    public function up(): QueryStatement
    {
        // Users without a profile get one of their own, which is linked through a temporary column.
        // Afterwards, every user must have a profile, so a profile can't be deleted while it's still in use.
        return new CompoundStatement(
            new AlterTableStatement('profiles')
                ->add(new IntegerStatement('user_id', nullable: true)),
            new RawStatement('INSERT INTO profiles (name, user_id) SELECT name, id FROM users WHERE profile_id IS NULL'),
            new RawStatement('UPDATE users INNER JOIN profiles ON profiles.user_id = users.id SET users.profile_id = profiles.id'),
            new AlterTableStatement('profiles')
                ->dropColumn('user_id'),
            new AlterTableStatement('users')
                ->dropConstraint('fk_profiles_users_profile_id')
                ->modify(new IntegerStatement('profile_id')),
            // The constraint is added again in a separate statement, because MySQL can't drop and add a constraint with the same name at once
            new AlterTableStatement('users')
                ->add(new BelongsToStatement('users.profile_id', 'profiles.id', onDelete: OnDelete::RESTRICT)),
        );
    }
}
