<?php

namespace App\Dungeon\Cards;

use App\Dungeon\Dungeon;
use App\Dungeon\Card;
use App\Dungeon\CheckBeforePlaying;
use App\Dungeon\Rarity;
use App\Dungeon\Type;
use App\Dungeon\Commands\RemoveDweller;
use App\Dungeon\Commands\SpawnDweller;
use App\Dungeon\Level;

final class UpperHandMajor implements Card, CheckBeforePlaying
{
    use IsCard;

    private(set) string $name = 'Upper Hands';

    private(set) string $description = 'Scare away all dwellers in sight';

    private(set) int $mana = 100;

    private(set) Rarity $rarity = Rarity::RARE;

    private(set) string $image = '/cards/upperhand-major.png';

    private(set) int $price = 2500;

    private(set) Type $type = Type::IMMEDIATE;

    private(set) Level $level = Level::NOVICE;

    public function play(Dungeon $dungeon): void
    {
        foreach ($dungeon->loopVisibleDwellers() as $dweller) {
            $dungeon->despawnDweller($dweller);
            $dungeon->spawnDweller();
        }
    }

    public function canPlay(Dungeon $dungeon): bool
    {
        return iterator_count($dungeon->loopVisibleDwellers()) > 0;
    }
}
