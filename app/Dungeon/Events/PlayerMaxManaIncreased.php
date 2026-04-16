<?php

namespace App\Dungeon\Events;

use App\Dungeon\DungeonEvent;

final class PlayerMaxManaIncreased implements DungeonEvent
{
    public string $name = 'player.maxManaIncreased';

    public array $payload {
        get => [
            'amount' => $this->amount,
        ];
    }

    public function __construct(
        public int $amount,
    ) {}
}