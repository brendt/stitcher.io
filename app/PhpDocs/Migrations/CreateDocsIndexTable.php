<?php

namespace App\PhpDocs\Migrations;

use Tempest\Database\MigratesUp;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;

final class CreateDocsIndexTable implements MigratesUp
{
    public string $name = '2025-10-23_create_docs_index_table';

    public function up(): QueryStatement
    {
        return new CreateTableStatement('index')
            ->primary()
            ->string('title')
            ->string('uri')
            ->unique('uri');
    }
}