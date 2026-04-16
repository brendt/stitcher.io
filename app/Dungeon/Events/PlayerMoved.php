<?php

namespace App\Dungeon\Events;

use App\Dungeon\DungeonEvent;
use App\Dungeon\Point;

final class PlayerMoved implements DungeonEvent
{
    public string $name = 'player.moved';

    public array $payload {
        get => [
            'from' => [
                'x' => $this->from->x,
                'y' => $this->from->y,
            ],
            'to' => [
                'x' => $this->to->x,
                'y' => $this->to->y,
            ],
        ];
    }

    public function __construct(
        public readonly Point $from,
        public readonly Point $to,
    ) {}
}