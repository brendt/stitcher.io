<?php

namespace App\Support\Authentication;

use Tempest\Database\MigratesUp;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\AlterTableStatement;
use Tempest\Database\QueryStatements\BelongsToStatement;
use Tempest\Database\QueryStatements\CompoundStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;
use Tempest\Database\QueryStatements\IntegerStatement;
use Tempest\Database\QueryStatements\OnDelete;
use Tempest\Database\QueryStatements\RawStatement;

final class CreateProfilesTable implements MigratesUp
{
    public string $name = '2026-11-02-001_create_profiles_table';

    public function up(): QueryStatement
    {
        // Every existing user gets a profile of their own, with the same id to easily link them
        return new CompoundStatement(
            new CreateTableStatement('profiles')
                ->primary()
                ->string('name'),
            new RawStatement('INSERT INTO profiles (id, name) SELECT id, name FROM users'),
            new AlterTableStatement('users')
                ->add(new IntegerStatement('profile_id', nullable: true))
                ->add(new BelongsToStatement('users.profile_id', 'profiles.id', onDelete: OnDelete::SET_NULL)),
            new RawStatement('UPDATE users SET profile_id = id'),
        );
    }
}
