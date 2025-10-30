<?php

namespace App\Map\Tile;

use App\Map\MapGame;

trait ClickableResourceTile
{
    public function handleClick(MapGame $game): void
    {
        $selectedItem = $game->selectedItem;

        if ($selectedItem?->canInteract($game, $this) && $this->item === null) {
            $this->item = $selectedItem;
            $game->buyItem($selectedItem);
        } else {
            $handHeldItem = $game->getHandHeldItemForTile($this);
            $game->incrementResource($this->getResource(), $handHeldItem?->getModifier() ?? 1);
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
}
