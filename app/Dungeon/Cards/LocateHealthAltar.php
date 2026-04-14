<?php

namespace App\Dungeon\Cards;

use App\Dungeon\Board;
use App\Dungeon\Cards\Support\Card;
use App\Dungeon\Cards\Support\CardTrait;
use App\Dungeon\Cards\Support\Rarity;
use App\Dungeon\Cards\Support\Type;
use App\Dungeon\Level;

final readonly class LocateHealthAltar implements Card
{
    use CardTrait;

    public function getName(): string
    {
        return "Locate Health Altar";
    }

    public function getDescription(): string
    {
        return "Locate one Health Altar";
    }

    public function play(Board $board): void
    {
        foreach ($board->healthAltarPoints as $healthAltarPoint) {
            if ($board->getTile($healthAltarPoint)) {
                continue;
            }

            $board->generateTile(null, $healthAltarPoint);

            break;
        }
    }

    public function getImage(): string
    {
        return '/cards/locate-health-altar.png';
    }

    public function getMana(): int
    {
        return 70;
    }

    public function getRarity(): Rarity
    {
        return Rarity::COMMON;
    }

    public function getPrice(): int
    {
        return 5000;
    }

    public function getType(): Type
    {
        return Type::IMMEDIATE;
    }

    public function getLevel(): Level
    {
        return Level::MASTER;
    }
}
