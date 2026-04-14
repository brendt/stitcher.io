<?php

namespace App\Dungeon\Cards;

use App\Dungeon\Board;
use App\Dungeon\Cards\Support\Card;
use App\Dungeon\Cards\Support\CardTrait;
use App\Dungeon\Cards\Support\Rarity;
use App\Dungeon\Cards\Support\Type;
use App\Dungeon\Commands\ChangeStability;
use App\Dungeon\Level;

final readonly class Clarity implements Card
{
    use CardTrait;

    public function getName(): string
    {
        return "Clarity";
    }

    public function getDescription(): string
    {
        return "+1 visibility, +20 stability";
    }

    public function play(Board $board): void
    {
        $board->visibilityRadius += 1;
        command(new ChangeStability(20));
    }

    public function getImage(): string
    {
        return '/cards/clarity-major.png';
    }

    public function getMana(): int
    {
        return 30;
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
        return Type::IMMEDIATE;
    }

    public function getLevel(): Level
    {
        return Level::NOOB;
    }
}
