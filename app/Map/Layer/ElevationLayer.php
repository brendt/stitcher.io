<?php

namespace App\Map\Layer;

use App\Map\Noise\PerlinGenerator;
use App\Map\Tile\GenericTile\BaseTile;
use App\Map\Tile\Tile;
use Illuminate\Support\Facades\Cache;

final readonly class ElevationLayer implements Layer
{
    public function __construct(
        private PerlinGenerator $generator,
    ) {}

    public function generate(Tile $tile, BaseLayer $base): Tile
    {
        if (! $tile instanceof BaseTile) {
            return $tile;
        }

        $elevation = $this->generator->noise($tile->x, $tile->y, 0, 60);

        $elevation = ($elevation / 2) + .5;

        return $tile->setElevation($elevation);
    }
}
