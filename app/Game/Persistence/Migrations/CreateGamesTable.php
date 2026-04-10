<?php

declare(strict_types=1);

namespace App\Game\Persistence\Migrations;

use Tempest\Database\MigratesUp;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;

final class CreateGamesTable implements MigratesUp
{
    public string $name = '2026-04-09-001_create_games_table';

    public function up(): QueryStatement
    {
        return new CreateTableStatement('games')
            ->primary('id', uuid: true)
            ->integer('seed', unsigned: true)
            ->string('status')
            ->datetime('created_at', current: true);
    }
}
