<?php

declare(strict_types=1);

namespace App\Game\Persistence\Migrations;

use Tempest\Database\MigratesUp;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;

final class CreateGameEdgesTable implements MigratesUp
{
    public string $name = '2026-04-09-004_create_game_edges_table';

    public function up(): QueryStatement
    {
        return new CreateTableStatement('game_edges')
            ->primary()
            ->string('game_id')
            ->string('from_station_id')
            ->string('to_station_id')
            ->integer('travel_time_seconds', unsigned: true)
            ->boolean('is_express', default: false)
            ->index('game_id');
    }
}
