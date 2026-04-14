<?php

namespace App\Dungeon\Cards;

use App\Dungeon\Dungeon;
use App\Dungeon\Card;
use App\Dungeon\Rarity;
use App\Dungeon\Type;
use App\Dungeon\Commands\ChangeStability;
use App\Dungeon\Level;

final class StabilityMinor implements Card
{
    use IsCard;

    private(set) string $name = "Stability";

    private(set) string $description = "+25 stability";

    private(set) string $image = '/cards/stability-minor.png';

    private(set) int $mana = 10;

    private(set) Rarity $rarity = Rarity::COMMON;

    private(set) int $price = 250;

    private(set) Type $type = Type::IMMEDIATE;

    private(set) Level $level = Level::NOOB;

    public function play(Dungeon $dungeon): void
    {
        // command(new ChangeStability(25));
    }
}
