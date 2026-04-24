<?php

namespace App\Dungeon\Events;

use App\Dungeon\Card;
use App\Dungeon\DungeonEvent;

final class DeckUpdated implements DungeonEvent
{
    public string $name = 'deck.updated';

    public array $payload {
        get => [
            'deck' => array_map(fn(Card $card) => $card->toArray(), $this->hand),
        ];
    }

    public function __construct(
        public readonly array $hand,
    ) {}
}