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
        if (! $card instanceof BreakthroughMajor && ! $card instanceof BreakthroughMinor) {
            return null;
        }

        $count = $deck->filter(fn(Card $card) => $card instanceof BreakthroughMajor || $card instanceof BreakthroughMinor)->count();

        if ($count >= 5) {
            return new DeckValidationFailed('You can only have 5 breakthrough cards in your hand');
        }

        return null;
    }
}