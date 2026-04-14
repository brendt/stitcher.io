<?php

namespace App\Dungeon\Cards;

use App\Dungeon\Dungeon;
use App\Dungeon\CanBuyWithShards;
use App\Dungeon\Card;
use App\Dungeon\Rarity;
use App\Dungeon\Type;
use App\Dungeon\Commands\ChangeStability;
use App\Dungeon\Commands\ResetTileCoins;
use App\Dungeon\Level;

final class Greed implements Card, CanBuyWithShards
{
    use IsCard;

    private(set) string $name = 'Greed';

    private(set) string $description = "+ all coins, -20 stability";

    private(set) string $image = '/cards/greed.png';

    private(set) int $mana = 140;

    private(set) Rarity $rarity = Rarity::EPIC;

    private(set) int $price = 10_000;

    private(set) Type $type = Type::IMMEDIATE;

    private(set) Level $level = Level::GRANDMASTER;

    public function play(Dungeon $dungeon): void
    {
        // foreach ($board->getTiles() as $tile) {
        // $board->coins += $tile->coins;
        // command(new ResetTileCoins($tile->point));
        // }
        // command(new ChangeStability(20));
    }

    public function getAdjustedPrice(): int
    {
        return round($this->price / 3);
    }

    public function getShardPrice(): int
    {
        return 10;
    }
}
