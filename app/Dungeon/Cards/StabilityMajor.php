<?php

namespace App\Dungeon\Cards;

use App\Dungeon\Dungeon;
use App\Dungeon\Card;
use App\Dungeon\Rarity;
use App\Dungeon\Type;
use App\Dungeon\Commands\ChangeStability;
use App\Dungeon\Level;

final class StabilityMajor implements Card
{
    use IsCard;

    private(set) string $name = "Stability++";

    private(set) string $description = "+50 stability";

    private(set) string $image = '/cards/stability-major.png';

    private(set) int $mana = 50;

    private(set) Rarity $rarity = Rarity::RARE;

    private(set) int $price = 1000;

    private(set) Type $type = Type::IMMEDIATE;

    private(set) Level $level = Level::NOVICE;

    public function play(Dungeon $dungeon): void
    {
        // command(new ChangeStability(50));
    }
}
