<?php

namespace App\Dungeon\Cards;

use App\Dungeon\Board;
use App\Dungeon\Cards\Support\CanBuyWithShards;
use App\Dungeon\Cards\Support\Card;
use App\Dungeon\Cards\Support\CardTrait;
use App\Dungeon\Cards\Support\Rarity;
use App\Dungeon\Cards\Support\Type;
use App\Dungeon\Commands\ChangeStability;
use App\Dungeon\Commands\ResetTileCoins;
use App\Dungeon\Level;

final readonly class Greed implements Card, CanBuyWithShards
{
    use CardTrait;

    public function getName(): string
    {
        return 'Greed';
    }

    public function getDescription(): string
    {
        return "+ all coins, -20 stability";
    }

    public function getImage(): string
    {
        return '/cards/greed.png';
    }

    public function play(Board $board): void
    {
        foreach ($board->getTiles() as $tile) {
            $board->coins += $tile->coins;
            command(new ResetTileCoins($tile->point));
        }

        command(new ChangeStability(20));
    }

    public function getMana(): int
    {
        return 140;
    }

    public function getRarity(): Rarity
    {
        return Rarity::EPIC;
    }

    public function getPrice(): int
    {
        return 10_000;
    }

    public function getType(): Type
    {
        return Type::IMMEDIATE;
    }

    public function getLevel(): Level
    {
        return Level::GRANDMASTER;
    }

    public function getAdjustedPrice(): int
    {
        return round($this->getPrice() / 3);
    }

    public function getShardPrice(): int
    {
        return 10;
    }
}
