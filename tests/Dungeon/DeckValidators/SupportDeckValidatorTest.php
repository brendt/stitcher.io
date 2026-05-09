<?php

namespace Tests\Dungeon\DeckValidators;

use App\Dungeon\Cards\HealMinor;
use App\Dungeon\Cards\SupportMajor;
use App\Dungeon\Cards\SupportMinor;
use App\Dungeon\DeckValidators\SupportDeckValidator;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Support\Arr\ImmutableArray;

final class SupportDeckValidatorTest extends TestCase
{
    #[Test]
    public function validate_returns_null_for_non_support_card(): void
    {
        $validator = new SupportDeckValidator();
        $deck = new ImmutableArray(array_fill(0, 5, new SupportMinor()));

        $result = $validator->validate(new HealMinor(), $deck);

        $this->assertNull($result);
    }

    #[Test]
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

        $this->assertNull($result);
    }

    #[Test]
    public function validate_returns_failure_when_deck_already_has_5_support_cards(): void
    {
        $validator = new SupportDeckValidator();
        $deck = new ImmutableArray(array_fill(0, 5, new SupportMinor()));

        $result = $validator->validate(new SupportMajor(), $deck);

        $this->assertNotNull($result);
        $this->assertSame('You can only have 5 support cards in your deck', $result->message);
    }
}
