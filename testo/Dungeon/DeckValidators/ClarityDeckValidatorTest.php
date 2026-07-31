<?php

declare(strict_types=1);

namespace Tests\Testo\Dungeon\DeckValidators;

use App\Dungeon\Cards\Clarity;
use App\Dungeon\Cards\HealMinor;
use App\Dungeon\DeckValidators\ClarityDeckValidator;
use Tempest\Support\Arr\ImmutableArray;
use Testo\Assert;
use Testo\Test;

#[Test]
final class ClarityDeckValidatorTest
{
    public function validate_returns_null_for_non_clarity_card(): void
    {
        $validator = new ClarityDeckValidator();
        $deck = new ImmutableArray(array_fill(0, 3, new Clarity()));

        $result = $validator->validate(new HealMinor(), $deck);

        Assert::null($result);
    }

    public function validate_returns_null_when_deck_has_fewer_than_3_clarity_cards(): void
    {
        $validator = new ClarityDeckValidator();
        $deck = new ImmutableArray([new Clarity(), new Clarity()]);

        $result = $validator->validate(new Clarity(), $deck);

        Assert::null($result);
    }

    public function validate_returns_failure_when_deck_already_has_3_clarity_cards(): void
    {
        $validator = new ClarityDeckValidator();
        $deck = new ImmutableArray(array_fill(0, 3, new Clarity()));

        $result = $validator->validate(new Clarity(), $deck);

        Assert::notNull($result);
        Assert::same($result->message, 'You can only have 3 clarity cards in your deck');
    }
}
