<?php

namespace App\Dungeon\Events;

use App\Dungeon\DungeonEvent;
use App\Dungeon\Tile;

final class TileGenerated implements DungeonEvent
{
    public string $name = 'tile.generated';

    public array $payload {
        get => $this->tile->toArray();
    }

    public function __construct(
        public readonly Tile $tile,
    ) {}
}