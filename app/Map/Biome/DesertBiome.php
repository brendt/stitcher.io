<?php

namespace App\Map\Biome;

use App\Map\Tile\GenericTile\BaseTile;

final class DesertBiome implements Biome
{
    public function getGrassColor(BaseTile $tile): string
    {
        $r = hex($tile->elevation / 1.5);
        $g = hex($tile->elevation);

        return "#{$r}{$g}00";
    }

    public function getWaterColor(BaseTile $tile): string
    {
        $r = hex($tile->elevation / 3);
        $g = hex($tile->elevation / 3);
        $b = hex($tile->elevation);

        return "#{$r}{$g}{$b}";
    }

    public function getTreeColor(BaseTile $tile): string
    {
        return 'pink';
    }
}
