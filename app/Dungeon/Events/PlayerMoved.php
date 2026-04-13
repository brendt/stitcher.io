<?php

namespace App\Dungeon\Events;

use App\Dungeon\Point;
use App\Dungeon\Support\DungeonEvent;

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