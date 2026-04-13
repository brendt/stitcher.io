<?php

namespace App\Dungeon\Events;

use App\Dungeon\Point;
use App\Dungeon\Support\DungeonEvent;

final class PlayerManaLost implements DungeonEvent
{
    public string $name = 'player.manaLost';

    public array $payload {
        get => [
            'amount' => $this->amount,
        ];
    }

    public function __construct(
        public readonly int $amount,
    ) {}
}