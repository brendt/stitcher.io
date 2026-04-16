<?php

namespace App\Dungeon\Persistence;

use Tempest\Database\MigratesUp;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;

final class CreateDungeonUserCardsTable implements MigratesUp
{
    public string $name = '2026-04-16_create_dungeon_user_cards_table';

    public function up(): QueryStatement
    {
        return CreateTableStatement::forModel(DungeonUserCard::class)
            ->primary()
            ->integer('userId', unsigned: true)
            ->integer('campaignId', unsigned: true)
            ->string('cardName')
            ->boolean('isActive')
            ->index('userId', 'campaignId');
    }
}