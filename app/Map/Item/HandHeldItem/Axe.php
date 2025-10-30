<?php

namespace App\Map\Item\HandHeldItem;

use App\Map\Item\HandHeldItem;
use App\Map\Item\ItemPrice;
use App\Map\MapGame;
use App\Map\Tile\ResourceTile\TreeTile;
use App\Map\Tile\Tile;

final class Axe implements HandHeldItem
{
    public function getId(): string
    {
        return 'Axe';
    }

    public function getName(): string
    {
        return 'Axe';
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
        return $tile instanceof TreeTile;
    }

    public function getModifier(): int
    {
        return 2;
    }
}
