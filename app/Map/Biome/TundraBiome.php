<?php

namespace App\Map\Biome;

use App\Map\Tile\GenericTile\BaseTile;

final class TundraBiome implements Biome
{
    public function getGrassColor(BaseTile $tile): string
    {
        $g = hex($tile->elevation);
        $b = hex($tile->elevation / 1.4);

        return "#00{$g}{$b}";
    }

    public function getWaterColor(BaseTile $tile): string
    {
        $hex = hex($tile->elevation);

        return "#0000{$hex}";
    }

    public function getTreeColor(BaseTile $tile): string
    {
        return 'pink';
    }
}
