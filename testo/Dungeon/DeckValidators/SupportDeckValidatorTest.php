<?php

declare(strict_types=1);

namespace Tests\Testo\Dungeon\DeckValidators;

use App\Dungeon\Cards\HealMinor;
use App\Dungeon\Cards\SupportMajor;
use App\Dungeon\Cards\SupportMinor;
use App\Dungeon\DeckValidators\SupportDeckValidator;
use Tempest\Support\Arr\ImmutableArray;
use Testo\Assert;
use Testo\Test;

#[Test]
final class SupportDeckValidatorTest
{
    public function validate_returns_null_for_non_support_card(): void
    {
        $validator = new SupportDeckValidator();
        $deck = new ImmutableArray(array_fill(0, 5, new SupportMinor()));

        $result = $validator->validate(new HealMinor(), $deck);

        Assert::null($result);
    }

    public function validate_returns_null_when_deck_has_fewer_than_5_support_cards(): void
    {
        $validator = new SupportDeckValidator();
        $deck = new ImmutableArray([
            new SupportMinor(),
            new SupportMajor(),
            new SupportMinor(),
            new SupportMajor(),
        ]);

        $result = $validator->validate(new SupportMinor(), $deck);

        Assert::null($result);
    }

    public function validate_returns_failure_when_deck_already_has_5_support_cards(): void
    {
        $validator = new SupportDeckValidator();
        $deck = new ImmutableArray(array_fill(0, 5, new SupportMinor()));

        $result = $validator->validate(new SupportMajor(), $deck);

        Assert::notNull($result);
        Assert::same($result->message, 'You can only have 5 support cards in your deck');
    }
}
