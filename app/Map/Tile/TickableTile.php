<?php

namespace App\Map\Tile;

use App\Map\MapGame;

trait TickableTile
{
    public function handleTicks(MapGame $game, int $ticks): void
    {
        $this->item?->handleTicks($game, $this, $ticks);
    }
}
