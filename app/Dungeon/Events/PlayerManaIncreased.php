<?php

namespace App\Dungeon\Events;

use App\Dungeon\DungeonEvent;

final class PlayerManaIncreased implements DungeonEvent
{
    public string $name = 'player.manaIncreased';

    public array $payload {
        get => [
            'amount' => $this->amount,
        ];
    }

    public function __construct(
        public readonly int $amount,
    ) {}
}