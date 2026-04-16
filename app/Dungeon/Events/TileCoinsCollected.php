<?php

namespace App\Dungeon\Events;

use App\Dungeon\DungeonEvent;
use App\Dungeon\Tile;

final class TileCoinsCollected implements DungeonEvent
{
    public string $name = 'tile.coinsCollected';

    public array $payload {
        get => [
            'tile' => $this->tile->toArray(),
            'amount' => $this->amount,
        ];
    }

    public function __construct(
        public Tile $tile,
        public int $amount,
    ) {}
}