<?php

namespace App\Map\Item\HandHeldItem;

use App\Map\Item\HandHeldItem;
use App\Map\Item\ItemPrice;
use App\Map\MapGame;
use App\Map\Tile\ResourceTile\FlaxTile;
use App\Map\Tile\Tile;

final class Shears implements HandHeldItem
{
    public function getId(): string
    {
        return 'Shears';
    }

    public function getName(): string
    {
        return 'Shears';
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
        return $tile instanceof FlaxTile;
    }

    public function getModifier(): int
    {
        return 2;
    }
}
