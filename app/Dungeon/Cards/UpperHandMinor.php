<?php

namespace App\Dungeon\Cards;

use App\Dungeon\Dungeon;
use App\Dungeon\Card;
use App\Dungeon\CheckBeforePlaying;
use App\Dungeon\Rarity;
use App\Dungeon\Type;
use App\Dungeon\Level;

final class UpperHandMinor implements Card, CheckBeforePlaying
{
    use IsCard;

    private(set) string $name = 'Upper Hand';

    private(set) string $description = 'Scare away 1 dweller in sight';

    private(set) int $mana = 50;

    private(set) Rarity $rarity = Rarity::COMMON;

    private(set) string $image = '/cards/upperhand-minor.png';

    private(set) int $price = 1000;

    private(set) Type $type = Type::IMMEDIATE;

    private(set) Level $level = Level::NOOB;

    public function play(Dungeon $dungeon): void
    {
        foreach ($dungeon->loopVisibleDwellers() as $dweller) {
            $dungeon->despawnDweller($dweller);
            $dungeon->spawnDweller();
            break;
        }
    }

    public function canPlay(Dungeon $dungeon): bool
    {
        return iterator_count($dungeon->loopVisibleDwellers()) > 0;
    }
}
