<?php

namespace App\Map\Tile\ResourceTile;

use App\Map\Biome\Biome;
use App\Map\Item\TileItem;
use App\Map\MapGame;
use App\Map\Tile\ClickableResourceTile;
use App\Map\Tile\GenericTile\BaseTile;
use App\Map\Tile\ResourceTile;
use App\Map\Tile\TickableTile;

final class StoneVeinTile extends BaseTile implements ResourceTile
{
    use ClickableResourceTile;
    use TickableTile;

    public function __construct(
        public readonly int $x,
        public readonly int $y,
        public readonly ?float $temperature,
        public readonly ?float $elevation,
        public readonly ?Biome $biome,
        public readonly float $noise,
        public ?TileItem $item = null,
    ) {}

    public function getColor(): string
    {
        $value = $this->noise;

        while ($value > 0.8) {
            $value -= 0.3;
        }

        $hex = hex($value);

        return "#{$hex}{$hex}{$hex}";
    }

    public function getBorderColor(): string
    {
        return '#333';
    }

    public function handleTicks(MapGame $game, int $ticks): void
    {
        $this->item?->handleTicks($game, $this, $ticks);
    }

    public function getResource(): Resource
    {
        return Resource::Stone;
    }

    public function getItem(): ?TileItem
    {
        return $this->item;
    }
}
