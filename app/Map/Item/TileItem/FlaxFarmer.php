<?php

namespace App\Map\Item\TileItem;

use App\Map\Item\ItemPrice;
use App\Map\Item\TileItem;
use App\Map\MapGame;
use App\Map\Tile\ResourceTile\FlaxTile;
use App\Map\Tile\Tile;

final class FlaxFarmer implements TileItem
{
    public function getId(): string
    {
        return 'FlaxFarmer';
    }

    public function getName(): string
    {
        return 'Flax Farmer';
    }

    public function canInteract(MapGame $game, Tile $tile): bool
    {
        return $tile instanceof FlaxTile;
    }

    public function handleTicks(MapGame $game, Tile $tile, int $ticks): void
    {
        $game->flaxCount += $ticks;
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
