<?php

namespace App\Dungeon\Cards;

use App\Dungeon\Board;
use App\Dungeon\Cards\Support\Card;
use App\Dungeon\Cards\Support\CardTrait;
use App\Dungeon\Cards\Support\Rarity;
use App\Dungeon\Cards\Support\Type;
use App\Dungeon\Commands\ChangeHealth;
use App\Dungeon\Commands\ChangeMaxHealth;
use App\Dungeon\Level;

final readonly class HealthIncreaseMinor implements Card
{
    use CardTrait;

    public function getName(): string
    {
        return "Health Potion";
    }

    public function getDescription(): string
    {
        return "+25 max health, +15 health";
    }

    public function play(Board $board): void
    {
        command(new ChangeMaxHealth(25));
        command(new ChangeHealth(15));
    }

    public function getImage(): string
    {
        return '/cards/health-increase-minor.png';
    }

    public function getMana(): int
    {
        return 120;
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
