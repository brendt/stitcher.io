<?php

namespace App\Dungeon\DeckValidators;

use App\Dungeon\Card;
use App\Dungeon\Cards\LocateHealthAltar;
use App\Dungeon\Cards\LocateManaAltar;
use App\Dungeon\Cards\LocateShard;
use App\Dungeon\Cards\LocateStabilityAltar;
use App\Dungeon\Cards\LocateVictoryPoint;
use App\Dungeon\DeckValidationFailed;
use App\Dungeon\DeckValidator;
use Tempest\Support\Arr\ImmutableArray;

final class LocateDeckValidator implements DeckValidator
{
    public function validate(Card $card, ImmutableArray $deck): ?DeckValidationFailed
    {
        $cardsToCheck = [
            LocateStabilityAltar::class,
            LocateManaAltar::class,
            LocateHealthAltar::class,
            LocateShard::class,
            LocateVictoryPoint::class,
        ];

        if (! in_array($card::class, $cardsToCheck)) {
            return null;
        }

        $count = $deck->filter(fn(Card $card) => in_array($card::class, $cardsToCheck))->count();

        if ($count >= 3) {
            return new DeckValidationFailed('You can only have 3 locate cards in your deck');
        }

        return null;
    }
}