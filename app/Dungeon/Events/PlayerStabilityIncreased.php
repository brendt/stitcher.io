<?php

namespace App\Dungeon\Events;

use App\Dungeon\Support\DungeonEvent;

final class PlayerStabilityIncreased implements DungeonEvent
{
    public string $name = 'player.stabilityIncreased';

    public array $payload {
        get => [
            'amount' => $this->amount,
        ];
    }

    public function __construct(
        public int $amount,
    ) {}
}