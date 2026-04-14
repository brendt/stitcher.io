<?php

namespace App\Dungeon\Cards;

use App\Dungeon\Board;
use App\Dungeon\Cards\Support\Card;
use App\Dungeon\Cards\Support\CardTrait;
use App\Dungeon\Cards\Support\InteractsWithTile;
use App\Dungeon\Cards\Support\Rarity;
use App\Dungeon\Cards\Support\Type;
use App\Dungeon\Commands\ChangeStability;
use App\Dungeon\Commands\RemoveTileWalls;
use App\Dungeon\Level;
use App\Dungeon\Tile;

final readonly class BreakthroughMinor implements Card, InteractsWithTile
{
    use CardTrait;

    public function getName(): string
    {
        return 'Breakthrough';
    }

    public function getDescription(): string
    {
        return "Remove a wall, -20 stability";
    }

    public function play(Board $board): void
    {
        $board->setActiveCard($this);
    }

    public function canInteractWithTile(Board $board, Tile $tile): bool
    {
        return ! $tile->isCollapsed;
    }

    public function interactWithTile(Board $board, Tile $tile): void
    {
        command(new RemoveTileWalls($tile->point));
        command(new ChangeStability(-20));
        $board->discardActiveCard();
    }

    public function getImage(): string
    {
        return '/cards/breakthrough-minor.png';
    }

    public function getMana(): int
    {
        return 20;
    }

    public function getRarity(): Rarity
    {
        return Rarity::COMMON;
    }

    public function getPrice(): int
    {
        return 100;
    }

    public function getType(): Type
    {
        return Type::ACTIVE;
    }

    public function getLevel(): Level
    {
        return Level::NOOB;
    }
}
