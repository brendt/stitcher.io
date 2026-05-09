<?php

namespace App\Dungeon\Events;

use App\Dungeon\DungeonEvent;
use App\Dungeon\Tile;

final class RelicCollected implements DungeonEvent
{
    public string $name = 'relic.collected';

    public array $payload {
        get => [
            'tile' => $this->tile->toArray(),
            'message' => "You collected a relic! (+{$this->coins} coins)",
        ];
    }

    public function __construct(
        public readonly Tile $tile,
        public readonly int $coins,
    ) {}
}