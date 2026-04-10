<?php

declare(strict_types=1);

namespace App\Game\Persistence\Migrations;

use Tempest\Database\MigratesUp;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\AlterTableStatement;
use Tempest\Database\QueryStatements\IntegerStatement;
use Tempest\Database\QueryStatements\TextStatement;

final class AddCoordinatesToGameStationsTable implements MigratesUp
{
    public string $name = '2026-04-10-001_add_coordinates_to_game_stations_table';

    public function up(): QueryStatement
    {
        return new AlterTableStatement('game_stations')
            ->add(new IntegerStatement(name: 'x', nullable: true))
            ->add(new IntegerStatement(name: 'y', nullable: true))
            ->add(new TextStatement(name: 'line_id', nullable: true));
    }
}
