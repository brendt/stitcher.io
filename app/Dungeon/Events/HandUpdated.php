<?php

namespace App\Dungeon\Events;

use App\Dungeon\Card;
use App\Dungeon\DungeonEvent;

final class HandUpdated implements DungeonEvent
{
    public string $name = 'hand.updated';

    public array $payload {
        get => [
            'hand' => array_map(fn(Card $card) => $card->toArray(), $this->hand),
        ];
    }

    public function __construct(
        public readonly array $hand,
    ) {}
}