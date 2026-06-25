<?php

namespace App\Dungeon\DeckValidators;

use App\Dungeon\Card;
use App\Dungeon\Cards\BreakthroughMajor;
use App\Dungeon\Cards\BreakthroughMinor;
use App\Dungeon\DeckValidationFailed;
use App\Dungeon\DeckValidator;
use Tempest\Support\Arr\ImmutableArray;

final class BreakthroughDeckValidator implements DeckValidator
{
    public function validate(Card $card, ImmutableArray $deck): ?DeckValidationFailed
    {
        $cardsToCheck = [
            BreakthroughMinor::class,
            BreakthroughMajor::class,
        ];

        if (! in_array($card::class, $cardsToCheck, strict: true)) {
            return null;
        }

        $count = $deck->filter(fn (Card $card) => in_array($card::class, $cardsToCheck, strict: true))->count();

        if ($count >= 5) {
            return new DeckValidationFailed('You can only have 5 breakthrough cards in your deck');
        }

        return null;
    }
}
