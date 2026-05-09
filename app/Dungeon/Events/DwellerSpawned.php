<?php

namespace App\Dungeon\Events;

use App\Dungeon\DungeonEvent;
use App\Dungeon\Dweller;

final class DwellerSpawned implements DungeonEvent
{
    public string $name = 'dweller.spawned';

    public array $payload {
        get => [
            'dweller' => $this->dweller->toArray(),
        ];
    }

    public function __construct(
        public readonly Dweller $dweller,
    ) {}
}