<?php

namespace App\Dungeon\Events;

use App\Dungeon\DungeonEvent;
use App\Dungeon\Dweller;

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