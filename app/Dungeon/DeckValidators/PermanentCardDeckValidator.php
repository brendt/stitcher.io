<?php

namespace App\Dungeon\DeckValidators;

use App\Dungeon\Card;
use App\Dungeon\DeckValidationFailed;
use App\Dungeon\DeckValidator;
use Tempest\Support\Arr\ImmutableArray;

final class PermanentCardDeckValidator implements DeckValidator
{
    public function validate(Card $card, ImmutableArray $deck): ?DeckValidationFailed
    {
        if (! $card->type->isPermanent()) {
            return null;
        }

        $count = $deck->filter(fn (Card $card) => $card->type->isPermanent())->count();

        if ($count >= 3) {
            return new DeckValidationFailed('You can only have 3 permanent cards in your deck');
        }

        return null;
    }
}