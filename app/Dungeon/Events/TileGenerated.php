<?php

namespace App\Dungeon\Events;

use App\Dungeon\Entities\Tile;
use App\Dungeon\Support\DungeonEvent;

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