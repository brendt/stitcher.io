<?php

namespace App\Dungeon\Cards;

use App\Dungeon\Board;
use App\Dungeon\Cards\Support\Card;
use App\Dungeon\Cards\Support\CardTrait;
use App\Dungeon\Cards\Support\Rarity;
use App\Dungeon\Cards\Support\Type;
use App\Dungeon\Commands\ChangeMaxMana;
use App\Dungeon\Level;

final readonly class ManaIncreaseMinor implements Card
{
    use CardTrait;

    public function getName(): string
    {
        return "Mana Potion";
    }

    public function getDescription(): string
    {
        return "+25 max mana";
    }

    public function play(Board $board): void
    {
        command(new ChangeMaxMana(25));
    }

    public function getImage(): string
    {
        return '/cards/mana-increase-minor.png';
    }

    public function getMana(): int
    {
        return 130;
    }

    public function getRarity(): Rarity
    {
        return Rarity::RARE;
    }

    public function getPrice(): int
    {
        return 1500;
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
