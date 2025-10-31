<?php

namespace App\Map\Tile\GenericTile;

use App\Map\Biome\Biome;
use App\Map\Tile\Tile;

class BaseTile implements Tile
{
    public function __construct(
        public int $x,
        public int $y,
        public ?float $temperature = null,
        public ?float $elevation = null,
        public ?Biome $biome = null,
    ) {}

    public function getColor(): string
    {
        return '#fff';
    }

    public function setTemperature(float $temperature): self
    {
        $clone = clone $this;

        $clone->temperature = $temperature;

        return $clone;
    }

    public function setElevation(float $elevation): self
    {
        $clone = clone $this;

        $clone->elevation = $elevation;

        return $clone;
    }

    public function setBiome(Biome $biome): self
    {
        $clone = clone $this;

        $clone->biome = $biome;

        return $clone;
    }

    public function getBiome(): ?Biome
    {
        return $this->biome;
    }

    public function getX(): int
    {
        return $this->x ?? dd($this);
    }

    public function getY(): int
    {
        return $this->y;
    }
}
