<?php

namespace App\Dungeon\Events;

use App\Dungeon\DungeonEvent;

final class PlayerMaxHealthIncreased implements DungeonEvent
{
    public string $name = 'player.maxHealthIncreased';

    public array $payload {
        get => [
            'amount' => $this->amount,
        ];
    }

    public function __construct(
        public int $amount,
    ) {}
}