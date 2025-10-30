<?php

namespace App\Map\Layer;

use App\Map\Biome\PlainsBiome;
use App\Map\Noise\PerlinGenerator;
use App\Map\Tile\ResourceTile\FlaxTile;
use App\Map\Tile\GenericTile\LandTile;
use App\Map\Tile\Tile;

final readonly class FlaxLayer implements Layer
{
    public function __construct(
        private PerlinGenerator $generator,
    ) {}

    public function generate(Tile $tile, BaseLayer $base): Tile
    {
        if (! $tile instanceof LandTile) {
            return $tile;
        }

        if ($tile->getBiome()::class !== PlainsBiome::class) {
            return $tile;
        }

        $noise = $this->generator->noise($tile->x, $tile->y, 0, 3);

        if ($noise < 0.5 || $noise > 0.6) {
            return $tile;
        }

        return new FlaxTile(
            x: $tile->x,
            y: $tile->y,
            temperature: $tile->temperature,
            elevation: $tile->elevation,
            biome: $tile->biome,
            noise: $noise
        );
    }
}
