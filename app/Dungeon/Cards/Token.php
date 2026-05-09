<?php

namespace App\Dungeon\Cards;

use App\Dungeon\Card;
use App\Dungeon\Dungeon;
use App\Dungeon\Level;
use App\Dungeon\Rarity;
use App\Dungeon\Type;

final class Token implements Card
{
    use IsCard;

    public string $name = 'Token';

    public string $description = 'Buy one additional token';

    public int $mana = 0;

    public Rarity $rarity  = Rarity::COMMON;

    public Type $type = Type::META;

    public string $image = '/cards/token.png';

    public int $price = 2500;

    public Level $level = Level::NOOB;

    public function play(Dungeon $dungeon): void
    {
        // Nothing on play
    }
}