<?php

namespace App\Dungeon\Persistence;

use Tempest\Database\MigratesUp;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;

final class CreateDungeonUserStatsTable implements MigratesUp
{
    public string $name = '2026-04-16_create_dungeon_user_stats_table';

    public function up(): QueryStatement
    {
        return CreateTableStatement::forModel(DungeonUserStats::class)
            ->primary()
            ->integer('userId', unsigned: true)
            ->integer('campaignId', unsigned: true)
            ->integer('coins', unsigned: true)
            ->integer('tokens', unsigned: true)
            ->integer('victoryPoints', unsigned: true)
            ->integer('experience', unsigned: true)
            ->integer('wins', unsigned: true)
            ->integer('losses', unsigned: true)
            ->integer('games', unsigned: true)
            ->integer('shards', unsigned: true)
            ->integer('runPrice', unsigned: true)
            ->unique('userId', 'campaignId');
    }
}