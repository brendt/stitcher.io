<?php

namespace App\Dungeon\DeckValidators;

use App\Dungeon\Card;
use App\Dungeon\Cards\StabilityMajor;
use App\Dungeon\Cards\StabilityMinor;
use App\Dungeon\Cards\SupportEpic;
use App\Dungeon\Cards\SupportMajor;
use App\Dungeon\Cards\SupportMinor;
use App\Dungeon\DeckValidationFailed;
use App\Dungeon\DeckValidator;
use Tempest\Support\Arr\ImmutableArray;

final class StabilityDeckValidator implements DeckValidator
{
    public function validate(Card $card, ImmutableArray $deck): ?DeckValidationFailed
    {
        $cardsToCheck = [
            StabilityMajor::class,
            StabilityMinor::class,
        ];

        if (! in_array($card::class, $cardsToCheck)) {
            return null;
        }

        $count = $deck->filter(fn(Card $card) => in_array($card::class, $cardsToCheck))->count();

        if ($count >= 7) {
            return new DeckValidationFailed('You can only have 7 stability cards in your deck');
        }

        return null;
    }
}