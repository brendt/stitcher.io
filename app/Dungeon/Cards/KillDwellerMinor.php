<?php

namespace App\Dungeon\Cards;

use App\Dungeon\Board;
use App\Dungeon\Cards\Support\Card;
use App\Dungeon\Cards\Support\CardTrait;
use App\Dungeon\Cards\Support\InteractsWithTile;
use App\Dungeon\Cards\Support\Rarity;
use App\Dungeon\Cards\Support\Type;
use App\Dungeon\Commands\DiscardActiveCard;
use App\Dungeon\Commands\RemoveDweller;
use App\Dungeon\Level;
use App\Dungeon\Tile;

final class KillDwellerMinor implements Card, InteractsWithTile
{
    use CardTrait;

    public function getName(): string
    {
        return 'Slice and Dice';
    }

    public function getDescription(): string
    {
        return "Kill 1 Dweller";
    }

    public function play(Board $board): void
    {
        $board->setActiveCard($this);
    }

    public function canInteractWithTile(Board $board, Tile $tile): bool
    {
        return $board->getDweller($tile->point) !== null;
    }

    public function interactWithTile(Board $board, Tile $tile): void
    {
        command(new RemoveDweller($tile->point));
        command(new DiscardActiveCard());
    }

    public function getImage(): string
    {
        return '/cards/kill-dweller-minor.png';
    }

    public function getMana(): int
    {
        return 75;
    }

    public function getRarity(): Rarity
    {
        return Rarity::COMMON;
    }

    public function getPrice(): int
    {
        return 1000;
    }

    public function getType(): Type
    {
        return Type::ACTIVE;
    }

    public function getLevel(): Level
    {
        return Level::NOVICE;
    }
}
