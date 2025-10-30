<?php

namespace App\Map\Item\TileItem;

use App\Map\Item\ItemPrice;
use App\Map\Item\TileItem;
use App\Map\MapGame;
use App\Map\Tile\ResourceTile\GoldVeinTile;
use App\Map\Tile\Tile;

final class GoldVeinFarmer implements TileItem
{
    public function getId(): string
    {
        return 'GoldVeinFarmer';
    }

    public function getName(): string
    {
        return 'Gold Vein Farmer';
    }

    public function canInteract(MapGame $game, Tile $tile): bool
    {
        return $tile instanceof GoldVeinTile;
    }

    public function handleTicks(MapGame $game, Tile $tile, int $ticks): void
    {
        $game->goldCount += $ticks;
    }

    public function getPrice(): ItemPrice
    {
        return new ItemPrice(
            wood: 50,
            gold: 20,
            stone: 20,
        );
    }

    public function getModifier(): int
    {
        return 1;
    }
}
