<?php

namespace App\Map\Item\TileItem;

use App\Map\Item\ItemPrice;
use App\Map\Item\TileItem;
use App\Map\MapGame;
use App\Map\Tile\ResourceTile\FishTile;
use App\Map\Tile\Tile;

final class FishFarmer implements TileItem
{
    public function getId(): string
    {
        return 'FishFarmer';
    }

    public function getName(): string
    {
        return 'Fish Farmer';
    }

    public function canInteract(MapGame $game, Tile $tile): bool
    {
        return $tile instanceof FishTile;
    }

    public function handleTicks(MapGame $game, Tile $tile, int $ticks): void
    {
        $game->fishCount += $ticks;
    }

    public function getPrice(): ItemPrice
    {
        return new ItemPrice(
            wood: 20,
            stone: 20,
        );
    }

    public function getModifier(): int
    {
        return 1;
    }
}
