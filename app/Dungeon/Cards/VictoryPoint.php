<?php

namespace App\Dungeon\Cards;

use App\Dungeon\Card;
use App\Dungeon\Dungeon;
use App\Dungeon\Level;
use App\Dungeon\Rarity;
use App\Dungeon\Type;

final class VictoryPoint implements Card
{
    use IsCard;

    public string $name = 'Victory Point';

    public string $description = 'Buy one victory point';

    public int $mana = 0;

    public Rarity $rarity  = Rarity::COMMON;

    public Type $type = Type::META;

    public string $image = '/cards/victory.png';

    public int $price = 5000;

    public Level $level = Level::NOVICE;

    public function play(Dungeon $dungeon): void
    {
        // Nothing on play
    }
}