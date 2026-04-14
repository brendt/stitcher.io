<?php

namespace App\Dungeon\Events;

use App\Dungeon\Card;
use App\Dungeon\Support\DungeonEvent;

final class CardPlayed implements DungeonEvent
{
    public string $name = 'card.played';

    public array $payload {
        get => [
            'card' => $this->card->toArray(),
        ];
    }

    public function __construct(
        private Card $card,
    ) {}
}