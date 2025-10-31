<?php

namespace App\Map\Tile\ResourceTile;

use App\Map\Biome\Biome;
use App\Map\Item\TileItem;
use App\Map\MapGame;
use App\Map\Tile\ClickableResourceTile;
use App\Map\Tile\GenericTile\BaseTile;
use App\Map\Tile\ResourceTile;
use App\Map\Tile\TickableTile;

final class GoldVeinTile extends BaseTile implements ResourceTile
{
    use ClickableResourceTile;
    use TickableTile;

    public function __construct(
        public int $x,
        public int $y,
        public ?float $temperature,
        public ?float $elevation,
        public ?Biome $biome,
        public readonly float $noise,
        public ?TileItem $item = null,
    ) {}

    public function getColor(): string
    {
        return '#777';
    }

    public function getBorderColor(): string
    {
        return '#FFEC53';
    }

    public function handleTicks(MapGame $game, int $ticks): void
    {
        $this->item?->handleTicks($game, $this, $ticks);
    }

    public function getResource(): Resource
    {
        return Resource::Gold;
    }

    public function getItem(): ?TileItem
    {
        return $this->item;
    }
}
