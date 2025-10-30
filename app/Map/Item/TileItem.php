<?php

namespace App\Map\Item;

use App\Map\MapGame;
use App\Map\Tile\Tile;

interface TileItem extends Item
{
    public function handleTicks(MapGame $game, Tile $tile, int $ticks): void;

    public function getModifier(): int;
}
