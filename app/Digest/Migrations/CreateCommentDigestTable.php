<?php

namespace App\Digest\Migrations;

use Tempest\Database\MigratesUp;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;

final class CreateCommentDigestTable implements MigratesUp
{
    public string $name = '2025-10-20_create_comment_digests_table';

    public function up(): QueryStatement
    {
        return new CreateTableStatement('comment_digests')
            ->primary()
            ->datetime('createdAt');
    }
}