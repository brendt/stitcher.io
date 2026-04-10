<?php

declare(strict_types=1);

namespace App\Game\Persistence\Migrations;

use Tempest\Database\MigratesUp;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;

final class CreateGameStationsTable implements MigratesUp
{
    public string $name = '2026-04-09-003_create_game_stations_table';

    public function up(): QueryStatement
    {
        return new CreateTableStatement('game_stations')
            ->primary()
            ->string('game_id')
            ->string('station_id')
            ->boolean('is_hub', default: false)
            ->index('game_id')
            ->index('station_id');
    }
}
