<?php

declare(strict_types=1);

namespace Tests\Testo\Dungeon\DeckValidators;

use App\Dungeon\Cards\HealMinor;
use App\Dungeon\Cards\StabilityMajor;
use App\Dungeon\Cards\StabilityMinor;
use App\Dungeon\DeckValidators\StabilityDeckValidator;
use Tempest\Support\Arr\ImmutableArray;
use Testo\Assert;
use Testo\Test;

#[Test]
final class StabilityDeckValidatorTest
{
    public function validate_returns_null_for_non_stability_card(): void
    {
        $validator = new StabilityDeckValidator();
        $deck = new ImmutableArray(array_fill(0, 7, new StabilityMinor()));

        $result = $validator->validate(new HealMinor(), $deck);

        Assert::null($result);
    }

    public function validate_returns_null_when_deck_has_fewer_than_7_stability_cards(): void
    {
        $validator = new StabilityDeckValidator();
        $deck = new ImmutableArray(array_fill(0, 6, new StabilityMinor()));

        $result = $validator->validate(new StabilityMajor(), $deck);

        Assert::null($result);
    }

    public function validate_returns_failure_when_deck_already_has_7_stability_cards(): void
    {
        $validator = new StabilityDeckValidator();
        $deck = new ImmutableArray(array_fill(0, 7, new StabilityMinor()));

        $result = $validator->validate(new StabilityMajor(), $deck);

        Assert::notNull($result);
        Assert::same($result->message, 'You can only have 7 stability cards in your deck');
    }
}
