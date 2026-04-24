<?php

namespace App\Dungeon\Cards;

use App\Dungeon\Dungeon;
use App\Dungeon\CanBuyWithShards;
use App\Dungeon\Card;
use App\Dungeon\LakePoint;
use App\Dungeon\Point;
use App\Dungeon\Rarity;
use App\Dungeon\Type;
use App\Dungeon\Level;

final class LocateLake implements Card
{
    use IsCard;

    private(set) string $name = "Locate Lake";

    private(set) string $description = "Locate a Lake";

    private(set) string $image = '/cards/spyglass.png';

    private(set) int $mana = 175;

    private(set) Rarity $rarity = Rarity::EPIC;

    private(set) int $price = 30_000;

    private(set) Type $type = Type::IMMEDIATE;

    private(set) Level $level = Level::GRANDMASTER;

    public function play(Dungeon $dungeon): void
    {
        foreach ($dungeon->lakes as $lake) {
            $point = array_first(iterator_to_array($lake->loopEdges()));

            if (! $point instanceof Point) {
                continue;
            }

            $tile = $dungeon->tryTile($point);

            if ($tile) {
                continue;
            }

            $dungeon->generateTile(null, $point);

            break;
        }
    }
}
