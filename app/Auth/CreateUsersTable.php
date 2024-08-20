<?php

namespace App\Auth;

use Tempest\Database\Migration;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;

final readonly class CreateUsersTable implements Migration
{
    public function getName(): string
    {
        return '001-create-users-table';
    }

    public function up(): QueryStatement|null
    {
        return (new CreateTableStatement('User'))
            ->primary()
            ->varchar('name')
            ->varchar('email');
    }

    public function down(): QueryStatement|null
    {
        return null;
    }
}