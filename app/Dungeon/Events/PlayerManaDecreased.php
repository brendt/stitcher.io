<?php

namespace App\Dungeon\Events;

use App\Dungeon\Point;
use App\Dungeon\Support\DungeonEvent;

final class PlayerManaDecreased implements DungeonEvent
{
    public string $name = 'player.manaDecreased';

    public array $payload {
        get => [
            'amount' => $this->amount,
        ];
    }

    public function __construct(
        public readonly int $amount,
    ) {}
}