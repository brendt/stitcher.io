<?php

namespace App\Dungeon;

use Tempest\Support\Arr\ImmutableArray;

interface DeckValidator
{
    public function validate(Card $card, ImmutableArray $deck): ?DeckValidationFailed;
}