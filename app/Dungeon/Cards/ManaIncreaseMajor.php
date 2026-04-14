<?php

namespace App\Dungeon\Cards;

use App\Dungeon\Board;
use App\Dungeon\Dungeon;
use App\Dungeon\Card;
use App\Dungeon\Rarity;
use App\Dungeon\Type;
use App\Dungeon\Commands\ChangeMaxMana;
use App\Dungeon\Level;

// TODO test
final class ManaIncreaseMajor implements Card
{
    use IsCard;

    private(set) string $name = "Large Mana Potion";

    private(set) string $description = "+50 max mana";

    private(set) string $image = '/cards/mana-increase-major.png';

    private(set) int $mana = 150;

    private(set) Rarity $rarity = Rarity::RARE;

    private(set) int $price = 3000;

    private(set) Type $type = Type::IMMEDIATE;

    private(set) Level $level = Level::MASTER;

    public function play(Dungeon $dungeon): void
    {
        // command(new ChangeMaxMana(50));
    }
}
