<?php

namespace App\Dungeon\Events;

use App\Dungeon\DungeonEvent;
use App\Dungeon\Lake;
use App\Dungeon\Tile;

final class LakeDiscovered implements DungeonEvent
{
    public string $name = 'lake.discovered';

    public array $payload {
        get => [
            'message' => 'You\'ve discovered a lake!'
        ];
    }

    public function __construct(
        public readonly Lake $lake,
        public readonly Tile $tile,
    ) {}
}