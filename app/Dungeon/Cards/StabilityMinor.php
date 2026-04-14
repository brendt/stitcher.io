<?php

namespace App\Dungeon\Cards;

use App\Dungeon\Board;
use App\Dungeon\Cards\Support\Card;
use App\Dungeon\Cards\Support\CardTrait;
use App\Dungeon\Cards\Support\Rarity;
use App\Dungeon\Cards\Support\Type;
use App\Dungeon\Commands\ChangeStability;
use App\Dungeon\Level;

final readonly class StabilityMinor implements Card
{
    use CardTrait;

    public function getName(): string
    {
        return "Stability";
    }

    public function getDescription(): string
    {
        return "+25 stability";
    }

    public function play(Board $board): void
    {
        command(new ChangeStability(25));
    }

    public function getImage(): string
    {
        return '/cards/stability-minor.png';
    }

    public function getMana(): int
    {
        return 10;
    }

    public function getRarity(): Rarity
    {
        return Rarity::COMMON;
    }

    public function getPrice(): int
    {
        return 250;
    }

    public function getType(): Type
    {
        return Type::IMMEDIATE;
    }

    public function getLevel(): Level
    {
        return Level::NOOB;
    }
}
