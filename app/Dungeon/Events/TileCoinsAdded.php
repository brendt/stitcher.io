<?php

namespace App\Dungeon\Events;

use App\Dungeon\Entities\Tile;
use App\Dungeon\Support\DungeonEvent;

final class TileCoinsAdded implements DungeonEvent
{
    public string $name = 'tile.coinsAdded';

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