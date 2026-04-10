<?php

declare(strict_types=1);

namespace App\Game\Persistence\Migrations;

use Tempest\Database\MigratesUp;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;

final class CreateGameStationClaimsTable implements MigratesUp
{
    public string $name = '2026-04-09-005_create_game_station_claims_table';

    public function up(): QueryStatement
    {
        return new CreateTableStatement('game_station_claims')
            ->primary()
            ->string('game_id')
            ->string('station_id')
            ->string('owner_id', nullable: true)
            ->integer('top_value', unsigned: true, default: 0)
            ->datetime('updated_at', current: true)
            ->index('game_id')
            ->index('station_id');
    }
}
