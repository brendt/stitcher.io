<?php

namespace App\Blog\Migrations;

use Tempest\Database\MigratesUp;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;

final class CreateCommentsTable implements MigratesUp
{
    public string $name = '2025-10-20_create_comments_table';

    public function up(): QueryStatement
    {
        return new CreateTableStatement('comments')
            ->primary()
            ->belongsTo('comments.user_id', 'users.id')
            ->string('for')
            ->text('content')
            ->index('for')
            ->datetime('createdAt');
    }
}