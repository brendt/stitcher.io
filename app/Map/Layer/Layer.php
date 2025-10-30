<?php

namespace App\Map\Layer;

use App\Map\Tile\Tile;

interface Layer
{
    public function generate(Tile $tile, BaseLayer $base): Tile;
}
