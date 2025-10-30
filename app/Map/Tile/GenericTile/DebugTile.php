<?php

namespace App\Map\Tile\GenericTile;

use App\Map\Biome\Biome;
use App\Map\Tile\Tile;

final readonly class DebugTile implements Tile
{
    public function __construct(
        public float $noise,
    ) {}

    public function getColor(): string
    {
        $hex = dechex($this->noise * 255);

        if (strlen($hex) < 2) {
            $hex = "0" . $hex;
        }

        return "#{$hex}{$hex}{$hex}";
    }

    public function getBiome(): ?Biome
    {
        return null;
    }
}
