<?php

namespace App\Dungeon\Repositories;

use App\Dungeon\Card;
use App\Dungeon\Support\CardConfig;

final readonly class CardRepository
{
    public function __construct(
        private CardConfig $cardConfig,
    ) {}

    /** @return \App\Dungeon\Card[] */
    public function getCards(): array
    {
        return $this->cardConfig->cards;
    }

    public function random(): Card
    {
        return $this->cardConfig->cards[array_rand($this->cardConfig->cards)];
    }

    public function findByName(string $name): ?Card
    {
        foreach ($this->cardConfig->cards as $card) {
            if ($card->name === $name) {
                return $card;
            }
        }
    }
}