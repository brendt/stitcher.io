<?php

namespace App\Dungeon\Events;

use App\Dungeon\DungeonEvent;

final class PlayerHealthIncreased implements DungeonEvent
{
    public string $name = 'player.healthIncreased';

    public array $payload {
        get => [
            'amount' => $this->amount,
            'total' => $this->total,
            'message' => $this->reason,
        ];
    }

    public function __construct(
        public readonly int $amount,
        public readonly int $total,
        public readonly ?string $reason = null,
    ) {}
}