<?php

namespace App\Dungeon\Events;

use App\Dungeon\Support\DungeonEvent;

final class PlayerStabilityDecreased implements DungeonEvent
{
    public string $name = 'player.stabilityDecreased';

    public array $payload {
        get => [
            'amount' => $this->amount,
        ];
    }

    public function __construct(
        public int $amount,
    ) {}
}