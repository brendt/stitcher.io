<?php

namespace App\Dungeon\Events;

use App\Dungeon\Support\DungeonEvent;

final class PlayerHealthDecreased implements DungeonEvent
{
    public string $name = 'player.healthDecreased';

    public array $payload {
        get => [
            'amount' => $this->amount,
        ];
    }

    public function __construct(
        public int $amount,
    ) {}
}