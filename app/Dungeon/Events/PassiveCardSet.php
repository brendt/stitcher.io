<?php

namespace App\Dungeon\Events;

use App\Dungeon\Card;
use App\Dungeon\DungeonEvent;

final class PassiveCardSet implements DungeonEvent
{
    public string $name = 'card.passsiveSet';

    public array $payload {
        get => [
            'card' => $this->card->toArray(),
        ];
    }

    public function __construct(
        public readonly Card $card,
    ) {}
}