<?php

namespace App\Aggregate\Suggestions\Migrations;

use Tempest\Database\MigratesUp;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\AlterTableStatement;
use Tempest\Database\QueryStatements\TextStatement;

final class AddTitleToSuggestionsTable implements MigratesUp
{
    public string $name = '2026-01-13-add-title-to-suggestions-table';

    public function up(): QueryStatement
    {
        return new AlterTableStatement('suggestions')
            ->add(new TextStatement(
                name: 'title',
                nullable: true,
            ));
    }
}