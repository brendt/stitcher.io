<?php

namespace App\Map\Biome;

use App\Map\Tile\GenericTile\BaseTile;

final class MesaBiome implements Biome
{
    public function getGrassColor(BaseTile $tile): string
    {
        $r = hex($tile->elevation);
        $g = hex($tile->elevation / 1.7);

        return "#{$r}{$g}00";
    }

    public function getWaterColor(BaseTile $tile): string
    {
        return 'pink';
    }

    public function getTreeColor(BaseTile $tile): string
    {
        return 'pink';
    }
}
