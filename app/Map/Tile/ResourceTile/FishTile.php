<?php

namespace App\Map\Tile\ResourceTile;

use App\Map\Biome\Biome;
use App\Map\Item\TileItem;
use App\Map\Tile\ClickableResourceTile;
use App\Map\Tile\GenericTile\BaseTile;
use App\Map\Tile\ResourceTile;
use App\Map\Tile\TickableTile;

final class FishTile extends BaseTile implements ResourceTile
{
    use ClickableResourceTile;
    use TickableTile;

    public function __construct(
        public int $x,
        public int $y,
        public ?float $temperature,
        public ?float $elevation,
        public ?Biome $biome,
        public float $noise,
        public ?TileItem $item = null,
    ) {}

    public function getColor(): string
    {
        $value = $this->noise;

        while ($value < 0.6) {
            $value += 0.1;
        }

        $hex = hex($value);

        return "#0000{$hex}";
    }

    public function getBorderColor(): string
    {
        return '#FFFFFF55';
    }

    public function getResource(): Resource
    {
        return Resource::Fish;
    }

    public function getItem(): ?TileItem
    {
        return $this->item;
    }
}
