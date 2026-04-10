<?php

declare(strict_types=1);

namespace App\Game\Persistence\Migrations;

use Tempest\Database\MigratesUp;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;

final class CreateGameEventsTable implements MigratesUp
{
    public string $name = '2026-04-09-006_create_game_events_table';

    public function up(): QueryStatement
    {
        return new CreateTableStatement('game_events')
            ->primary()
            ->string('game_id')
            ->string('player_id', nullable: true)
            ->string('type')
            ->json('payload', nullable: true)
            ->datetime('effective_at', nullable: true)
            ->integer('order_key', unsigned: true, nullable: true)
            ->datetime('created_at', current: true)
            ->index('game_id');
    }
}
