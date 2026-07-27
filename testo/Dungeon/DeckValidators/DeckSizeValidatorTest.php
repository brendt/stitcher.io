<?php

declare(strict_types=1);

namespace Tests\Testo\Dungeon\DeckValidators;

use App\Dungeon\Cards\HealMinor;
use App\Dungeon\DeckValidators\DeckSizeValidator;
use Tempest\Support\Arr\ImmutableArray;
use Testo\Assert;
use Testo\Test;

#[Test]
final class DeckSizeValidatorTest
{
    public function validate_returns_null_when_deck_has_fewer_than_20_cards(): void
    {
        $validator = new DeckSizeValidator();
        $deck = new ImmutableArray(array_fill(0, 19, new HealMinor()));

        $result = $validator->validate(new HealMinor(), $deck);

        Assert::null($result);
    }

    public function validate_returns_failure_when_deck_has_20_cards(): void
    {
        $validator = new DeckSizeValidator();
        $deck = new ImmutableArray(array_fill(0, 20, new HealMinor()));

        $result = $validator->validate(new HealMinor(), $deck);

        Assert::notNull($result);
        Assert::same($result->message, 'Your deck is full');
    }
}
