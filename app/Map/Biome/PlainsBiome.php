<?php

namespace App\Map\Biome;

use App\Map\Tile\GenericTile\BaseTile;

final class PlainsBiome implements Biome
{
    public function getGrassColor(BaseTile $tile): string
    {
        $g = hex($tile->elevation);
        $b = hex($tile->elevation / 4);

        return "#00{$g}{$b}";
    }

    public function getWaterColor(BaseTile $tile): string
    {
        $g = hex($tile->elevation / 3);
        $b = hex($tile->elevation);

        return "#00{$g}{$b}";
    }

    public function getTreeColor(BaseTile $tile): string
    {
        return 'pink';
    }
}
