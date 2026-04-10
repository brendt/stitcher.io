<?php

declare(strict_types=1);

namespace App\Game\Persistence\Migrations;

use Tempest\Database\MigratesUp;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;

final class CreateGameChallengesTable implements MigratesUp
{
    public string $name = '2026-04-09-007_create_game_challenges_table';

    public function up(): QueryStatement
    {
        return new CreateTableStatement('game_challenges')
            ->primary()
            ->string('game_id')
            ->string('station_id')
            ->boolean('active', default: true)
            ->integer('reward', unsigned: true)
            ->datetime('spawned_at', current: true)
            ->datetime('completed_at', nullable: true)
            ->index('game_id')
            ->index('station_id')
            ->index('active');
    }
}
