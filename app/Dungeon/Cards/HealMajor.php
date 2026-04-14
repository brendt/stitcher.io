<?php

namespace App\Dungeon\Cards;

use App\Dungeon\Card;
use App\Dungeon\Dungeon;
use App\Dungeon\Level;
use App\Dungeon\Rarity;
use App\Dungeon\Type;

final class HealMajor implements Card
{
    use IsCard;

    private(set) string $name = 'Heal++';

    private(set) string $description = '+50 health';

    private(set) string $image = '/cards/heal-major.png';

    private(set) int $mana = 60;

    private(set) Rarity $rarity = Rarity::COMMON;

    private(set) int $price = 1_000;

    private(set) Type $type = Type::IMMEDIATE;

    private(set) Level $level = Level::NOOB;

    public function play(Dungeon $dungeon): void
    {
        // TODO
//        command(new ChangeHealth(50));
    }
}
