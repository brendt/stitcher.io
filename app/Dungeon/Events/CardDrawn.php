<?php

namespace App\Dungeon\Events;

use App\Dungeon\Card;
use App\Dungeon\DungeonEvent;

final class CardDrawn implements DungeonEvent
{
    public string $name = 'card.drawn';

    public array $payload {
        get => [
            'card' => $this->card->toArray(),
        ];
    }

    public function __construct(
        private Card $card,
    ) {}
}