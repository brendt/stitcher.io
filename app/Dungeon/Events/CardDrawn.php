<?php

namespace App\Dungeon\Events;

use App\Dungeon\Card;
use App\Dungeon\Support\DungeonEvent;

final class CardDrawn implements DungeonEvent
{
    public string $name = 'card.drawn';

    public array $payload {
        get => [
            'card' => [
                'id' => $this->card->id
            ],
        ];
    }

    public function __construct(
        private Card $card,
    ) {}
}