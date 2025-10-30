<?php

namespace App\Map\Tile;

use App\Map\MapGame;

interface HandlesClick
{
    public function canClick(MapGame $game): bool;

    public function handleClick(MapGame $game): void;
}
