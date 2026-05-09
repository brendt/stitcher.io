<?php

namespace App\Dungeon\Cards;

use App\Dungeon\Dungeon;
use App\Dungeon\Card;
use App\Dungeon\Rarity;
use App\Dungeon\Type;
use App\Dungeon\Level;

final class Clarity implements Card
{
    use IsCard;

    private(set) string $name = "Clarity";

    private(set) string $description = "+1 visibility, +20 stability";

    private(set) string $image = '/cards/clarity-major.png';

    private(set) int $mana = 30;

    private(set) Rarity $rarity = Rarity::COMMON;

    private(set) int $price = 3500;

    private(set) Type $type = Type::IMMEDIATE;

    private(set) Level $level = Level::NOOB;

    public function play(Dungeon $dungeon): void
    {
        $dungeon->changeVisibility($dungeon->visibilityRadius + 1);
        $dungeon->increaseStability(20);
    }
}
