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

final readonly class HealthIncreaseMajor implements Card
{
    use CardTrait;

    public function getName(): string
    {
        return "Large Health Potion";
    }

    public function getDescription(): string
    {
        return "+50 max health, +40 health";
    }

    public function play(Board $board): void
    {
        command(new ChangeMaxHealth(50));
        command(new ChangeHealth(40));
    }

    public function getImage(): string
    {
        return '/cards/health-increase-major.png';
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
        return 2500;
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
