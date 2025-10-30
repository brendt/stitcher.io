<?php

namespace App\Map\Layer;

use App\Map\Biome\DesertBiome;
use App\Map\Biome\ForestBiome;
use App\Map\Noise\PerlinGenerator;
use App\Map\Tile\ResourceTile\GoldVeinTile;
use App\Map\Tile\GenericTile\DebugTile;
use App\Map\Tile\Tile;
use App\Map\Tile\ResourceTile\TreeTile;

final readonly class TreeLayer implements Layer
{
    public function __construct(
        private PerlinGenerator $generator,
    ) {}

    public function generate(Tile $tile, BaseLayer $base): Tile
    {
        if ($tile->getBiome()::class === ForestBiome::class) {
            $noise = $this->generator->noise($tile->x, $tile->y, 0, 3);

            if ($noise < 0) {
                return $tile;
            }

            return new TreeTile(
                x: $tile->x,
                y: $tile->y,
                temperature: $tile->temperature,
                elevation: $tile->elevation,
                biome: $tile->biome,
                noise: $noise,
            );
        }

        return $tile;
    }
}
