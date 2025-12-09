<?php

namespace App\Nws\Migrations;

use App\Nws\Nws;
use App\Nws\Sentiment;
use Tempest\Database\MigratesUp;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;

final class CreateNwsTable implements MigratesUp
{
    public string $name = '2025-12-25_create_nws_table';

    public function up(): QueryStatement
    {
        return CreateTableStatement::forModel(Nws::class)
            ->primary()
            ->string('uri')
            ->string('title')
            ->datetime('publishedAt')
            ->text('summary')
            ->string('tag')
            ->enum('sentiment', Sentiment::class, nullable: true)
            ->json('keywords', nullable: true);
    }
}