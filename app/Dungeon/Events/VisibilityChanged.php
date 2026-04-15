<?php

namespace App\Dungeon\Events;

use App\Dungeon\Support\DungeonEvent;

final class VisibilityChanged implements DungeonEvent
{
    public string $name = 'visibility.changed';

    public array $payload {
        get => [
            'visibilityRadius' => $this->visibilityRadius,
        ];
    }

    public function __construct(
        public readonly int $visibilityRadius,
    ) {}
}