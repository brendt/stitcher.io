<?php

namespace App\Dungeon\Cards;

use App\Dungeon\Dungeon;
use App\Dungeon\CanBuyWithShards;
use App\Dungeon\Card;
use App\Dungeon\Rarity;
use App\Dungeon\Type;
use App\Dungeon\Level;

final class LocateShard implements Card, CanBuyWithShards
{
    use IsCard;

    private(set) string $name = "Spyglass";

    private(set) string $description = "Locate a Shard";

    private(set) string $image = '/cards/spyglass.png';

    private(set) int $mana = 175;

    private(set) Rarity $rarity = Rarity::EPIC;

    private(set) int $price = 20000;

    private(set) Type $type = Type::IMMEDIATE;

    private(set) Level $level = Level::GRANDMASTER;

    public function play(Dungeon $dungeon): void
    {
        // foreach ($board->shardPoints as $shardPoint) {
        // if ($board->getTile($shardPoint)) {
        // continue;
        // }
        // $board->generateTile(null, $shardPoint);
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
