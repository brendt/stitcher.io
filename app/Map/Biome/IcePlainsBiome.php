<?php

namespace App\Map\Biome;

use App\Map\Tile\GenericTile\BaseTile;

final class IcePlainsBiome implements Biome
{
    public function getGrassColor(BaseTile $tile): string
    {
        $r = hex(0);
        $g = hex($tile->elevation);
        $b = hex($tile->elevation);

        return "#{$r}{$g}{$b}";
    }

    public function getWaterColor(BaseTile $tile): string
    {
        $noise = $tile->elevation;

        while ($noise < 0.9) {
            $noise += 0.1;
        }

        $r = hex($noise);
        $g = hex($noise);
        $b = hex($noise);

        return "#{$r}{$g}{$b}";
    }

    public function getTreeColor(BaseTile $tile): string
    {
        return 'pink';
    }
}
