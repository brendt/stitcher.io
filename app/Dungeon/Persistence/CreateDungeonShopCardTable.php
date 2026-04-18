<?php

namespace App\Dungeon\Persistence;

use Tempest\Database\MigratesUp;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;

final class CreateDungeonShopCardTable implements MigratesUp
{
    public string $name = '2026-04-16_create_dungeon_shop_card_table';

    public function up(): QueryStatement
    {
        return CreateTableStatement::forModel(DungeonShopCard::class)
            ->primary()
            ->integer('userId', unsigned: true)
            ->integer('campaignId', unsigned: true)
            ->string('cardName')
            ->integer('price', unsigned: true)
            ->index('userId', 'campaignId');
    }
}