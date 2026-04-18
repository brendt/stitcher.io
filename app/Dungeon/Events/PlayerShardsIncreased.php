<?php

namespace App\Dungeon\Events;

use App\Dungeon\DungeonEvent;

final class PlayerShardsIncreased implements DungeonEvent
{
    public string $name = 'player.shardsIncreased';

    public array $payload {
        get => [
            'amount' => $this->amount,
        ];
    }

    public function __construct(
        public int $amount,
    ) {}
}