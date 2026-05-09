<?php

namespace App\Dungeon\Cards;

use App\Dungeon\Dungeon;
use App\Dungeon\Card;
use App\Dungeon\Rarity;
use App\Dungeon\Type;
use App\Dungeon\Level;

final class LocateStabilityAltar implements Card
{
    use IsCard;

    private(set) string $name = "Locate Stability Altar";

    private(set) string $description = "Locate one Stability Altar";

    private(set) string $image = '/cards/locate-stability-altar.png';

    private(set) int $mana = 70;

    private(set) Rarity $rarity = Rarity::COMMON;

    private(set) int $price = 20_000;

    private(set) Type $type = Type::IMMEDIATE;

    private(set) Level $level = Level::MASTER;

    public function play(Dungeon $dungeon): void
    {
        foreach ($dungeon->loopStabilityAltar() as $altar) {
            $tile = $dungeon->tryTile($altar);

            if ($tile) {
                continue;
            }

            $dungeon->generateTile(null, $altar);

            break;
        }
    }
}
