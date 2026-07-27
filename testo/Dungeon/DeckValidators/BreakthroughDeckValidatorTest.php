<?php

declare(strict_types=1);

namespace Tests\Testo\Dungeon\DeckValidators;

use App\Dungeon\Cards\BreakthroughMajor;
use App\Dungeon\Cards\BreakthroughMinor;
use App\Dungeon\Cards\HealMinor;
use App\Dungeon\DeckValidators\BreakthroughDeckValidator;
use Tempest\Support\Arr\ImmutableArray;
use Testo\Assert;
use Testo\Test;

#[Test]
final class BreakthroughDeckValidatorTest
{
    public function validate_returns_null_for_non_breakthrough_card(): void
    {
        $validator = new BreakthroughDeckValidator();
        $deck = new ImmutableArray(array_fill(0, 5, new BreakthroughMinor()));

        $result = $validator->validate(new HealMinor(), $deck);

        Assert::null($result);
    }

    public function validate_returns_null_when_deck_has_fewer_than_5_breakthrough_cards(): void
    {
        $validator = new BreakthroughDeckValidator();
        $deck = new ImmutableArray([
            new BreakthroughMinor(),
            new BreakthroughMajor(),
            new BreakthroughMinor(),
            new BreakthroughMajor(),
        ]);

        $result = $validator->validate(new BreakthroughMinor(), $deck);

        Assert::null($result);
    }

    public function validate_returns_failure_when_deck_already_has_5_breakthrough_cards(): void
    {
        $validator = new BreakthroughDeckValidator();
        $deck = new ImmutableArray(array_fill(0, 5, new BreakthroughMinor()));

        $result = $validator->validate(new BreakthroughMajor(), $deck);

        Assert::notNull($result);
        Assert::same($result->message, 'You can only have 5 breakthrough cards in your deck');
    }
}
