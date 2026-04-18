<?php

namespace App\Dungeon\Events;

use App\Dungeon\DungeonEvent;
use App\Dungeon\Tile;

final class ArtifactCollected implements DungeonEvent
{
    public string $name = 'artifact.collected';

    public array $payload {
        get => $this->tile->toArray();
    }

    public function __construct(
        public readonly Tile $tile,
    ) {}
}