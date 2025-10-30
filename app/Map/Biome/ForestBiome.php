<?php

namespace App\Map\Biome;

use App\Map\Tile\GenericTile\BaseTile;

final class ForestBiome implements Biome
{
    public function getGrassColor(BaseTile $tile): string
    {
        $g = hex($tile->elevation / 1.5);

        return "#00{$g}00";
    }

    public function getWaterColor(BaseTile $tile): string
    {
        return 'blue';
    }

    public function getTreeColor(BaseTile $tile): string
    {
        return 'darkgreen';
    }
}
