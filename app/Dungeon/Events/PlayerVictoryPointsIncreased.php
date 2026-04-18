<?php

namespace App\Dungeon\Events;

use App\Dungeon\DungeonEvent;

final class PlayerVictoryPointsIncreased implements DungeonEvent
{
    public string $name = 'player.victoryPointsIncreased';

    public array $payload {
        get => [
            'amount' => $this->amount,
        ];
    }

    public function __construct(
        public int $amount,
    ) {}
}