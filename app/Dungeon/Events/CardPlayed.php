<?php

namespace App\Dungeon\Events;

use App\Dungeon\Card;
use App\Dungeon\DungeonEvent;

final class CardPlayed implements DungeonEvent
{
    public string $name = 'card.played';

    public array $payload {
        get => [
            'card' => $this->card->toArray(),
        ];
    }

    public function __construct(
        public readonly Card $card,
    ) {}
}