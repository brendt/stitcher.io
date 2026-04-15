<?php

namespace App\Dungeon\Events;

use App\Dungeon\Dweller;
use App\Dungeon\Support\DungeonEvent;

final class DwellerDespawned implements DungeonEvent
{
    public string $name = 'dweller.despawned';

    public array $payload {
        get => [
            'dweller' => $this->dweller->toArray(),
        ];
    }

    public function __construct(
        public readonly Dweller $dweller,
    ) {}
}