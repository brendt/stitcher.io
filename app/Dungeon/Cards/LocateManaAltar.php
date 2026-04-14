<?php

namespace App\Dungeon\Cards;

use App\Dungeon\Board;
use App\Dungeon\Cards\Support\Card;
use App\Dungeon\Cards\Support\CardTrait;
use App\Dungeon\Cards\Support\Rarity;
use App\Dungeon\Cards\Support\Type;
use App\Dungeon\Level;

final readonly class LocateManaAltar implements Card
{
    use CardTrait;

    public function getName(): string
    {
        return "Locate Mana Altar";
    }

    public function getDescription(): string
    {
        return "Locate one Mana Altar";
    }

    public function play(Board $board): void
    {
        foreach ($board->manaAltarPoints as $manaAltarPoint) {
            if ($board->getTile($manaAltarPoint)) {
                continue;
            }

            $board->generateTile(null, $manaAltarPoint);

            break;
        }
    }

    public function getImage(): string
    {
        return '/cards/locate-mana-altar.png';
    }

    public function getMana(): int
    {
        return 30;
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
