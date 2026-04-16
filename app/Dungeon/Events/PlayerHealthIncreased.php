<?php

namespace App\Dungeon\Events;

use App\Dungeon\DungeonEvent;

final class PlayerHealthIncreased implements DungeonEvent
{
    public string $name = 'player.healthIncreased';

    public array $payload {
        get => [
            'amount' => $this->amount,
        ];
    }

    public function __construct(
        public int $amount,
    ) {}
}