<?php

namespace App\Map\Tile\GenericTile;

use App\Map\Biome\Biome;
use App\Map\Item\HasMenu;
use App\Map\Item\TileItem;
use App\Map\Item\TileItem\TradingPost;
use App\Map\MapGame;
use App\Map\Menu;
use App\Map\Tile\HandlesClick;
use App\Map\Tile\HandlesTicks;
use App\Map\Tile\HasBorder;

final class LandTile extends BaseTile implements HandlesClick, HasBorder, HasMenu, HandlesTicks
{
    public function __construct(
        public readonly int $x,
        public readonly int $y,
        public readonly ?float $temperature,
        public readonly ?float $elevation,
        public readonly ?Biome $biome,
        public ?TileItem $item = null,
    ) {}

    public static function fromBase(BaseTile $tile): self
    {
        return new self(...(array) $tile);
    }

    public function getColor(): string
    {
        return $this->getBiome()->getGrassColor($this);
    }

    public function handleClick(MapGame $game): void
    {
        $selectedItem = $game->selectedItem;

        if ($selectedItem?->canInteract($game, $this) && $this->item === null) {
            $this->item = $selectedItem;
            $game->buyItem($selectedItem);

            return;
        }

        if ($this->item instanceof HasMenu) {
            $game->openMenu($this->item);
        }
    }

    public function canClick(MapGame $game): bool
    {
        $selectedItem = $game->selectedItem;

        if ($selectedItem) {
            return $selectedItem->canInteract($game, $this) && $this->item === null;
        }

        return true;
    }

    public function getBorderColor(): string
    {
        if ($this->item) {
            return 'red';
        }

        return '';
    }

    public function getMenu(): ?Menu
    {
        if (! $this->item instanceof HasMenu) {
            return null;
        }

        return $this->item->getMenu();
    }

    public function saveMenu(array $form): void
    {
        if (! $this->item instanceof HasMenu) {
            return;
        }

        $this->item->saveMenu($form);
    }

    public function handleTicks(MapGame $game, int $ticks): void
    {
        if (! $this->item instanceof TradingPost) {
            return;
        }

        $this->item->handleTicks($game, $this, $ticks);
    }
}
