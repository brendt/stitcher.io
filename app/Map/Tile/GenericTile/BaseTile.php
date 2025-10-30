<?php

namespace App\Map\Tile\GenericTile;

use App\Map\Biome\Biome;
use App\Map\Tile\Tile;
use Spatie\Cloneable\Cloneable;

class BaseTile implements Tile
{
    use Cloneable;

    public function __construct(
        public readonly int $x,
        public readonly int $y,
        public readonly ?float $temperature = null,
        public readonly ?float $elevation = null,
        public readonly ?Biome $biome = null,
    ) {}

    public function getColor(): string
    {
        return '#fff';
    }

    public function setTemperature(float $temperature): self
    {
        return $this->with(
            temperature: $temperature,
        );
    }

    public function setElevation(float $elevation): self
    {
        return $this->with(
            elevation: $elevation,
        );
    }

    public function setBiome(Biome $biome): self
    {
        return $this->with(
            biome: $biome,
        );
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
