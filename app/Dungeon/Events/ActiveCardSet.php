<?php

namespace App\Dungeon\Events;

use App\Dungeon\Card;
use App\Dungeon\DungeonEvent;

final class ActiveCardSet implements DungeonEvent
{
    public string $name = 'card.activeSet';

    public array $payload {
        get => [
            'card' => $this->card->toArray(),
        ];
    }

    public function __construct(
        public readonly Card $card,
    ) {}
}