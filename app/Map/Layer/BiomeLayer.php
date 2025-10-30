<?php

namespace App\Map\Layer;

use App\Map\Biome\Biome;
use App\Map\Biome\DesertBiome;
use App\Map\Biome\ForestBiome;
use App\Map\Biome\IcePlainsBiome;
use App\Map\Biome\MesaBiome;
use App\Map\Biome\PlainsBiome;
use App\Map\Biome\TundraBiome;
use App\Map\Tile\GenericTile\BaseTile;
use App\Map\Tile\Tile;

final readonly class BiomeLayer implements Layer
{
    public function generate(Tile $tile, BaseLayer $base): Tile
    {
        if (! $tile instanceof BaseTile) {
            return $tile;
        }

        $biome = match (true) {
            $tile->temperature < 0.1 => $this->iceBiome($tile),
            $tile->temperature < 0.4 => $this->coldBiome($tile),
            $tile->temperature < 0.8 => $this->warmBiome($tile),
            default => $this->hotBiome($tile),
        };

        return $tile->setBiome($biome);
    }

    private function iceBiome(BaseTile $tile): Biome
    {
        return new IcePlainsBiome();
    }

    private function coldBiome(BaseTile $tile): Biome
    {
        return new TundraBiome();
    }

    private function warmBiome(BaseTile $tile): Biome
    {
        return match (true) {
            $tile->elevation < 0.7 => new PlainsBiome(),
            default => new ForestBiome(),
        };
    }

    private function hotBiome(BaseTile $tile): Biome
    {
        return match (true) {
            $tile->elevation < 0.8 => new DesertBiome(),
            default => new MesaBiome(),
        };
    }
}
