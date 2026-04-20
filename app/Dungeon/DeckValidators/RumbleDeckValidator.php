<?php

namespace App\Dungeon\DeckValidators;

use App\Dungeon\Card;
use App\Dungeon\Cards\RumbleMinor;
use App\Dungeon\DeckValidationFailed;
use App\Dungeon\DeckValidator;
use Tempest\Support\Arr\ImmutableArray;

final class RumbleDeckValidator implements DeckValidator
{
    public function validate(Card $card, ImmutableArray $deck): ?DeckValidationFailed
    {
        $cardsToCheck = [
            RumbleMinor::class,
            RumbleMinor::class,
        ];

        if (! in_array($card::class, $cardsToCheck)) {
            return null;
        }

        $count = $deck->filter(fn(Card $card) => in_array($card::class, $cardsToCheck))->count();

        if ($count >= 5) {
            return new DeckValidationFailed('You can only have 5 rumble cards in your deck');
        }

        return null;
    }
}