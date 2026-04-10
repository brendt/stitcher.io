<?php

declare(strict_types=1);

namespace App\Game\Persistence\Migrations;

use Tempest\Database\MigratesUp;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\AlterTableStatement;
use Tempest\Database\QueryStatements\TextStatement;

final class AddTypeAndPlayerToGameChallengesTable implements MigratesUp
{
    public string $name = '2026-04-10-002_add_type_and_player_to_game_challenges_table';

    public function up(): QueryStatement
    {
        return new AlterTableStatement('game_challenges')
            ->add(new TextStatement(name: 'challenge_type', nullable: true))
            ->add(new TextStatement(name: 'player_id', nullable: true));
    }
}
