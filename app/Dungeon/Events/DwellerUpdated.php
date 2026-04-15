<?php

namespace App\Dungeon\Events;

use App\Dungeon\Dweller;
use App\Dungeon\Support\DungeonEvent;

final class DwellerUpdated implements DungeonEvent
{
    public string $name = 'dweller.updated';

    public array $payload {
        get => [
            'dweller' => $this->dweller->toArray(),
        ];
    }

    public function __construct(
        public readonly Dweller $dweller,
    ) {}
}