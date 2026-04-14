<?php

namespace App\Dungeon\Cards;

use App\Dungeon\Board;
use App\Dungeon\Cards\Support\Card;
use App\Dungeon\Cards\Support\CardTrait;
use App\Dungeon\Cards\Support\Rarity;
use App\Dungeon\Cards\Support\Type;
use App\Dungeon\Commands\ChangeHealth;
use App\Dungeon\Level;

final readonly class HealMinor implements Card
{
    use CardTrait;

    public function getName(): string
    {
        return "Heal";
    }

    public function getDescription(): string
    {
        return "+25 health";
    }

    public function play(Board $board): void
    {
        command(new ChangeHealth(25));
    }

    public function getImage(): string
    {
        return '/cards/heal-minor.png';
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
