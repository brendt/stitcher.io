<?php

namespace App\Dungeon\Cards;

use App\Dungeon\Card;
use App\Dungeon\Dungeon;
use App\Dungeon\Level;
use App\Dungeon\Rarity;
use App\Dungeon\Type;

final  class TestCard implements Card
{
    use IsCard;

    public string $name = 'test';

    public string $description = 'test';

    public int $mana = 1;

    public Rarity $rarity = Rarity::COMMON;

    public Type $type = Type::IMMEDIATE;

    public string $image = '/cards/artifact.png';

    public int $price  = 10;

    public Level $level = Level::NOOB;

    public function play(Dungeon $dungeon): void
    {
        // TODO: Implement play() method.
    }
}