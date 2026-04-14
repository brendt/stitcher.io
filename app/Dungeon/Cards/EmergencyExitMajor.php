<?php

namespace App\Dungeon\Cards;

use App\Dungeon\Board;
use App\Dungeon\Cards\Support\Card;
use App\Dungeon\Cards\Support\CardTrait;
use App\Dungeon\Cards\Support\Rarity;
use App\Dungeon\Cards\Support\Type;
use App\Dungeon\Level;

final readonly class EmergencyExitMajor implements Card
{
    use CardTrait;

    public function getName(): string
    {
        return "Emergency Exit++";
    }

    public function getDescription(): string
    {
        return "Exit the dungeon with 70% of collected coins.";
    }

    public function getMana(): int
    {
        return 200;
    }

    public function getRarity(): Rarity
    {
        return Rarity::EPIC;
    }

    public function getImage(): string
    {
        return "/cards/emergency-major.png";
    }

    public function getPrice(): int
    {
        return 10_000;
    }

    public function play(Board $board): void
    {
        $board->coins = $board->coins * 0.7;
        $board->exitDungeon();
    }

    public function getType(): Type
    {
        return Type::IMMEDIATE;
    }

    public function getLevel(): Level
    {
        return Level::GRANDMASTER;
    }
}
