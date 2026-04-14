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

// TODO test
final class UpperHandMinor implements Card, CheckBeforePlaying
{
    use IsCard;

    private(set) string $name = 'Upper Hand';

    private(set) string $description = 'Scare away 1 dweller in sight';

    private(set) int $mana = 50;

    private(set) Rarity $rarity = Rarity::COMMON;

    private(set) string $image = '/cards/upperhand-minor.png';

    private(set) int $price = 250;

    private(set) Type $type = Type::IMMEDIATE;

    private(set) Level $level = Level::NOOB;

    public function play(Dungeon $dungeon): void
    {
        // foreach ($board->getVisibleDwellers() as $dweller) {
        // command(new RemoveDweller($dweller->point));
        // command(new SpawnDweller());
        // break;
        // }
    }

    public function canPlay(Dungeon $dungeon): bool
    {
        return iterator_count($board->getVisibleDwellers()) > 0;
    }
}
