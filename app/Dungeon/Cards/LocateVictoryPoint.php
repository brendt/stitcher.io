<?php

namespace App\Dungeon\Cards;

use App\Dungeon\Board;
use App\Dungeon\Cards\Support\CanBuyWithShards;
use App\Dungeon\Cards\Support\Card;
use App\Dungeon\Cards\Support\CardTrait;
use App\Dungeon\Cards\Support\Rarity;
use App\Dungeon\Cards\Support\Type;
use App\Dungeon\Level;

final readonly class LocateVictoryPoint implements Card, CanBuyWithShards
{
    use CardTrait;

    public function getName(): string
    {
        return "Flare";
    }

    public function getDescription(): string
    {
        return "Locate a Victory Point";
    }

    public function play(Board $board): void
    {
        foreach ($board->victoryPointPoints as $victoryPointPoint) {
            if ($board->getTile($victoryPointPoint)) {
                continue;
            }

            $board->generateTile(null, $victoryPointPoint);

            break;
        }
    }

    public function getImage(): string
    {
        return '/cards/flare.png';
    }

    public function getMana(): int
    {
        return 200;
    }

    public function getRarity(): Rarity
    {
        return Rarity::EPIC;
    }

    public function getPrice(): int
    {
        return 30000;
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
