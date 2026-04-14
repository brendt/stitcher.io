<?php

namespace App\Dungeon\Cards;

use App\Dungeon\Board;
use App\Dungeon\Dungeon;
use App\Dungeon\Card;
use App\Dungeon\Rarity;
use App\Dungeon\Type;
use App\Dungeon\Commands\ChangeHealth;
use App\Dungeon\Commands\ChangeMaxHealth;
use App\Dungeon\Level;

final class HealthIncreaseMinor implements Card
{
    use IsCard;

    private(set) string $name = "Health Potion";

    private(set) string $description = "+25 max health, +15 health";

    private(set) string $image = '/cards/health-increase-minor.png';

    private(set) int $mana = 120;

    private(set) Rarity $rarity = Rarity::RARE;

    private(set) int $price = 1500;

    private(set) Type $type = Type::IMMEDIATE;

    private(set) Level $level = Level::NOVICE;

    public function play(Dungeon $dungeon): void
    {
        // command(new ChangeMaxHealth(25));
        // command(new ChangeHealth(15));
    }
}
