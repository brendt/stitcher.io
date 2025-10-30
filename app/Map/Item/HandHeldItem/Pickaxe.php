<?php

namespace App\Map\Item\HandHeldItem;

use App\Map\Item\HandHeldItem;
use App\Map\Item\ItemPrice;
use App\Map\MapGame;
use App\Map\Tile\ResourceTile\GoldVeinTile;
use App\Map\Tile\ResourceTile\StoneVeinTile;
use App\Map\Tile\Tile;

final class Pickaxe implements HandHeldItem
{
    public function getId(): string
    {
        return 'Pickaxe';
    }

    public function getName(): string
    {
        return 'Pickaxe';
    }

    public function getPrice(): ItemPrice
    {
        return new ItemPrice(
            wood: 20,
            stone: 20,
        );
    }

    public function canInteract(MapGame $game, Tile $tile): bool
    {
        return $tile instanceof StoneVeinTile
            || $tile instanceof GoldVeinTile;
    }

    public function getModifier(): int
    {
        return 2;
    }
}
