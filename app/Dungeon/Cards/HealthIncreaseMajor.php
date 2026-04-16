<?php

namespace App\Dungeon\Cards;

use App\Dungeon\Dungeon;
use App\Dungeon\Card;
use App\Dungeon\Rarity;
use App\Dungeon\Type;
use App\Dungeon\Level;

final class HealthIncreaseMajor implements Card
{
    use IsCard;

    private(set) string $name = "Large Health Potion";

    private(set) string $description = "+50 max health, +40 health";

    private(set) string $image = '/cards/health-increase-major.png';

    private(set) int $mana = 150;

    private(set) Rarity $rarity = Rarity::RARE;

    private(set) int $price = 2500;

    private(set) Type $type = Type::IMMEDIATE;

    private(set) Level $level = Level::MASTER;

    public function play(Dungeon $dungeon): void
    {
        $dungeon->increaseMaxHealth(50);
        $dungeon->increaseHealth(40);
    }
}
