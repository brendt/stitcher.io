<?php

namespace App\Dungeon\Cards;

use App\Dungeon\Board;
use App\Dungeon\Cards\Support\Card;
use App\Dungeon\Cards\Support\CardTrait;
use App\Dungeon\Cards\Support\Rarity;
use App\Dungeon\Cards\Support\Type;
use App\Dungeon\Commands\ChangeStability;
use App\Dungeon\Level;

final readonly class StabilityMajor implements Card
{
    use CardTrait;

    public function getName(): string
    {
        return "Stability++";
    }

    public function getDescription(): string
    {
        return "+50 stability";
    }

    public function play(Board $board): void
    {
        command(new ChangeStability(50));
    }

    public function getImage(): string
    {
        return '/cards/stability-major.png';
    }

    public function getMana(): int
    {
        return 50;
    }

    public function getRarity(): Rarity
    {
        return Rarity::RARE;
    }

    public function getPrice(): int
    {
        return 1000;
    }

    public function getType(): Type
    {
        return Type::IMMEDIATE;
    }

    public function getLevel(): Level
    {
        return Level::NOVICE;
    }
}
