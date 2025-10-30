<?php

namespace App\Map\Biome;

use App\Map\Tile\GenericTile\BaseTile;

interface Biome
{
    public function getGrassColor(BaseTile $tile): string;

    public function getWaterColor(BaseTile $tile): string;

    public function getTreeColor(BaseTile $tile): string;
}
