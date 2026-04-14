<?php

namespace App\Dungeon\Cards;

use App\Dungeon\Dungeon;
use App\Dungeon\Card;
use App\Dungeon\Rarity;
use App\Dungeon\Type;
use App\Dungeon\Commands\ChangeHealth;
use App\Dungeon\Level;

final class HealMinor implements Card
{
    use IsCard;

    private(set) string $name = "Heal";

    private(set) string $description = "+25 health";

    private(set) string $image = '/cards/heal-minor.png';

    private(set) int $mana = 30;

    private(set) Rarity $rarity = Rarity::COMMON;

    private(set) int $price = 250;

    private(set) Type $type = Type::IMMEDIATE;

    private(set) Level $level = Level::NOOB;

    public function play(Dungeon $dungeon): void
    {
        // command(new ChangeHealth(25));
    }
}
