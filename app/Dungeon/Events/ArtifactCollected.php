<?php

namespace App\Dungeon\Events;

use App\Dungeon\DungeonEvent;
use App\Dungeon\Tile;

final class ArtifactCollected implements DungeonEvent
{
    public string $name = 'artifact.collected';

    public array $payload {
        get => [
            'tile' => $this->tile->toArray(),
            'message' => "You collected an artifact! (+{$this->coins} coins)",
        ];
    }

    public function __construct(
        public readonly Tile $tile,
        public readonly int $coins,
    ) {}
}
