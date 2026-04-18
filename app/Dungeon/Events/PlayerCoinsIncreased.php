<?php

namespace App\Dungeon\Events;

use App\Dungeon\DungeonEvent;

final class PlayerCoinsIncreased implements DungeonEvent
{
    public string $name = 'player.coinsIncreased';

    public array $payload {
        get => [
            'amount' => $this->amount,
        ];
    }

    public function __construct(
        public readonly int $amount,
    ) {}
}