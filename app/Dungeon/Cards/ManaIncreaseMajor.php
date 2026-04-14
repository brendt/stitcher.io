<?php

namespace App\Dungeon\Cards;

use App\Dungeon\Board;
use App\Dungeon\Cards\Support\Card;
use App\Dungeon\Cards\Support\CardTrait;
use App\Dungeon\Cards\Support\Rarity;
use App\Dungeon\Cards\Support\Type;
use App\Dungeon\Commands\ChangeMaxMana;
use App\Dungeon\Level;

// TODO test
final readonly class ManaIncreaseMajor implements Card
{
    use CardTrait;

    public function getName(): string
    {
        return "Large Mana Potion";
    }

    public function getDescription(): string
    {
        return "+50 max mana";
    }

    public function play(Board $board): void
    {
        command(new ChangeMaxMana(50));
    }

    public function getImage(): string
    {
        return '/cards/mana-increase-major.png';
    }

    public function getMana(): int
    {
        return 150;
    }

    public function getRarity(): Rarity
    {
        return Rarity::RARE;
    }

    public function getPrice(): int
    {
        return 3000;
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
