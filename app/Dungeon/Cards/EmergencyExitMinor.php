<?php

namespace App\Dungeon\Cards;

use App\Dungeon\Board;
use App\Dungeon\Cards\Support\Card;
use App\Dungeon\Cards\Support\CardTrait;
use App\Dungeon\Cards\Support\Rarity;
use App\Dungeon\Cards\Support\Type;
use App\Dungeon\Level;

final readonly class EmergencyExitMinor implements Card
{
    use CardTrait;

    public function getName(): string
    {
        return "Emergency Exit";
    }

    public function getDescription(): string
    {
        return "Exit the dungeon with 20% of collected coins.";
    }

    public function getMana(): int
    {
        return 150;
    }

    public function getRarity(): Rarity
    {
        return Rarity::RARE;
    }

    public function getImage(): string
    {
        return "/cards/emergency-minor.png";
    }

    public function getPrice(): int
    {
        return 5000;
    }

    public function play(Board $board): void
    {
        $board->coins = $board->coins * 0.2;
        $board->exitDungeon();
    }

    public function getType(): Type
    {
        return Type::IMMEDIATE;
    }

    public function getLevel(): Level
    {
        return Level::MASTER;
    }
}
