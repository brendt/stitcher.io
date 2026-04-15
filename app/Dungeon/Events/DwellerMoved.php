<?php

namespace App\Dungeon\Events;

use App\Dungeon\Dweller;
use App\Dungeon\Point;
use App\Dungeon\Support\DungeonEvent;

final class DwellerMoved implements DungeonEvent
{
    public string $name = 'dweller.moved';

    public array $payload {
        get => [
            'dweller' => $this->dweller->toArray(),
            'from' => $this->from->toArray(),
            'to' => $this->to->toArray(),
            'isVisible' => $this->isVisible,
        ];
    }

    public function __construct(
        public readonly Dweller $dweller,
        public readonly Point $from,
        public readonly Point $to,
        public bool $isVisible,
    ) {}
}