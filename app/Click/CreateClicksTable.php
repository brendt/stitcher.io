<?php

namespace App\Click;

use Tempest\Database\MigratesUp;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;

final class CreateClicksTable implements MigratesUp
{
    public string $name = '2026-07-17-create_clicks_table';

    public function up(): QueryStatement
    {
        return CreateTableStatement::forModel(Click::class)
            ->primary()
            ->string('uri')
            ->integer('clicks', unsigned: true, default: 0)
            ->unique('uri');
    }
}