<?php

namespace App\Map\Tile;

use App\Map\Biome\Biome;

interface Tile
{
    public function getColor(): string;

    public function getBiome(): ?Biome;

    public function getX(): int;

    public function getY(): int;
}
