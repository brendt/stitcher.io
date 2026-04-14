<?php

namespace App\Dungeon\Cards;

use App\Dungeon\Board;
use App\Dungeon\Cards\Support\CanBuyWithShards;
use App\Dungeon\Cards\Support\Card;
use App\Dungeon\Cards\Support\CardTrait;
use App\Dungeon\Cards\Support\Rarity;
use App\Dungeon\Cards\Support\Type;
use App\Dungeon\Level;

final readonly class LocateShard implements Card, CanBuyWithShards
{
    use CardTrait;

    public function getName(): string
    {
        return "Spyglass";
    }

    public function getDescription(): string
    {
        return "Locate a Shard";
    }

    public function play(Board $board): void
    {
        foreach ($board->shardPoints as $shardPoint) {
            if ($board->getTile($shardPoint)) {
                continue;
            }

            $board->generateTile(null, $shardPoint);

            break;
        }
    }

    public function getImage(): string
    {
        return '/cards/spyglass.png';
    }

    public function getMana(): int
    {
        return 175;
    }

    public function getRarity(): Rarity
    {
        return Rarity::EPIC;
    }

    public function getPrice(): int
    {
        return 20000;
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
        return $this->getPrice();
    }

    public function getShardPrice(): int
    {
        return 20;
    }
}
