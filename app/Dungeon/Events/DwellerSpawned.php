<?php

namespace App\Dungeon\Events;

use App\Dungeon\Dweller;
use App\Dungeon\Support\DungeonEvent;

final class DwellerSpawned implements DungeonEvent
{
    public string $name = 'dweller.spawned';

    public array $payload {
        get => [
            'dweller' => $this->dweller->toArray(),
            'isVisible' => $this->isVisible,
        ];
    }

    public function __construct(
        public readonly Dweller $dweller,
        public bool $isVisible,
    ) {}
}