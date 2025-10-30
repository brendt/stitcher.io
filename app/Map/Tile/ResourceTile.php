<?php

namespace App\Map\Tile;

use App\Map\Item\TileItem;
use App\Map\Tile\ResourceTile\Resource;

interface ResourceTile extends HasBorder, HandlesClick, HandlesTicks
{
    public function getItem(): ?TileItem;

    public function getResource(): Resource;
}
