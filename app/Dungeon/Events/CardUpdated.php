<?php

namespace App\Dungeon\Events;

use App\Dungeon\Card;
use App\Dungeon\DungeonEvent;

final class CardUpdated implements DungeonEvent
{
    public string $name = 'card.updated';

    public array $payload {
        get => [
            'card' => $this->card->toArray(),
        ];
    }

    public function __construct(
        public readonly Card $card,
    ) {}
}