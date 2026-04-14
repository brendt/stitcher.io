<?php

namespace App\Dungeon\Cards;

use App\Dungeon\Board;
use App\Dungeon\Dungeon;
use App\Dungeon\Cards\Support\CanBuyWithShards;
use App\Dungeon\Card;
use App\Dungeon\Rarity;
use App\Dungeon\Type;
use App\Dungeon\Level;

final class LocateVictoryPoint implements Card, CanBuyWithShards
{
    use IsCard;

    private(set) string $name = "Flare";

    private(set) string $description = "Locate a Victory Point";

    private(set) string $image = '/cards/flare.png';

    private(set) int $mana = 200;

    private(set) Rarity $rarity = Rarity::EPIC;

    private(set) int $price = 30000;

    private(set) Type $type = Type::IMMEDIATE;

    private(set) Level $level = Level::GRANDMASTER;

    public function play(Dungeon $dungeon): void
    {
        // foreach ($board->victoryPointPoints as $victoryPointPoint) {
        // if ($board->getTile($victoryPointPoint)) {
        // continue;
        // }
        // $board->generateTile(null, $victoryPointPoint);
        // break;
        // }
    }

    public function getAdjustedPrice(): int
    {
        return $this->price;
    }

    public function getShardPrice(): int
    {
        return 20;
    }
}
