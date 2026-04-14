<?php

namespace App\Dungeon\Cards;

use App\Dungeon\Board;
use App\Dungeon\Dungeon;
use App\Dungeon\Card;
use App\Dungeon\Rarity;
use App\Dungeon\Type;
use App\Dungeon\Commands\ChangeMaxMana;
use App\Dungeon\Level;

final class ManaIncreaseMinor implements Card
{
    use IsCard;

    private(set) string $name = "Mana Potion";

    private(set) string $description = "+25 max mana";

    private(set) string $image = '/cards/mana-increase-minor.png';

    private(set) int $mana = 130;

    private(set) Rarity $rarity = Rarity::RARE;

    private(set) int $price = 1500;

    private(set) Type $type = Type::IMMEDIATE;

    private(set) Level $level = Level::NOVICE;

    public function play(Dungeon $dungeon): void
    {
        // command(new ChangeMaxMana(25));
    }
}
