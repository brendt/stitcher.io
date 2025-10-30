<?php

namespace App\Map\Layer;

use App\Map\Noise\PerlinGenerator;
use App\Map\Tile\GenericTile\BaseTile;
use App\Map\Tile\Tile;

final readonly class TemperatureLayer implements Layer
{
    public function __construct(
        private PerlinGenerator $generator,
    ) {}

    public function generate(Tile $tile, BaseLayer $base): Tile
    {
        if (! $tile instanceof BaseTile) {
            return $tile;
        }

        $temperature = $this->generator->noise($tile->x, $tile->y, 0, 45);

        $temperature = ($temperature / 2) + .5;

        return $tile->setTemperature($temperature);
    }
}
