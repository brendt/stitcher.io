<?php

namespace App\Dungeon\DeckValidators;

use App\Dungeon\Card;
use App\Dungeon\Cards\BreakthroughMajor;
use App\Dungeon\Cards\BreakthroughMinor;
use App\Dungeon\Cards\Clarity;
use App\Dungeon\DeckValidationFailed;
use App\Dungeon\DeckValidator;
use Tempest\Support\Arr\ImmutableArray;

final class ClarityDeckValidator implements DeckValidator
{
    public function validate(Card $card, ImmutableArray $deck): ?DeckValidationFailed
    {
        $cardsToCheck = [
            Clarity::class,
        ];

        if (! in_array($card::class, $cardsToCheck)) {
            return null;
        }

        $count = $deck->filter(fn(Card $card) => in_array($card::class, $cardsToCheck))->count();

        if ($count >= 3) {
            return new DeckValidationFailed('You can only have 3 clarity cards in your deck');
        }

        return null;
    }
}