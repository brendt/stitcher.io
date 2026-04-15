<?php

namespace App\Dungeon\Events;

use App\Dungeon\Point;
use App\Dungeon\Support\DungeonEvent;

final class ArtifactSpawned implements DungeonEvent
{
    public string $name = 'artifact.spawned';

    public array $payload {
        get => $this->point->toArray();
    }

    public function __construct(
        public readonly Point $point
    ) {}
}