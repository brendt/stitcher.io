<?php

namespace App\Dungeon\Events;

use App\Dungeon\DungeonEvent;
use App\Dungeon\Point;

final class ArtifactSpawned implements DungeonEvent
{
    public string $name = 'artifact.spawned';

    public array $payload {
        get => $this->point->toArray();
    }

    public function __construct(
        public readonly Point $point,
    ) {}
}