<?php

declare(strict_types=1);

namespace App\Game\Persistence\Migrations;

use Tempest\Database\MigratesUp;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;

final class CreateGamePlayersTable implements MigratesUp
{
    public string $name = '2026-04-09-002_create_game_players_table';

    public function up(): QueryStatement
    {
        return new CreateTableStatement('game_players')
            ->primary()
            ->string('game_id')
            ->string('player_id')
            ->integer('coins', unsigned: true)
            ->string('station_id', nullable: true)
            ->index('game_id')
            ->index('player_id');
    }
}
