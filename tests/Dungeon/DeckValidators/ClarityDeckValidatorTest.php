<?php

namespace Tests\Dungeon\DeckValidators;

use App\Dungeon\Cards\Clarity;
use App\Dungeon\Cards\HealMinor;
use App\Dungeon\DeckValidators\ClarityDeckValidator;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Support\Arr\ImmutableArray;

final class ClarityDeckValidatorTest extends TestCase
{
    #[Test]
    public function validate_returns_null_for_non_clarity_card(): void
    {
        $validator = new ClarityDeckValidator();
        $deck = new ImmutableArray(array_fill(0, 3, new Clarity()));

        $result = $validator->validate(new HealMinor(), $deck);

        $this->assertNull($result);
    }

    #[Test]
    public function validate_returns_null_when_deck_has_fewer_than_3_clarity_cards(): void
    {
        $validator = new ClarityDeckValidator();
        $deck = new ImmutableArray([new Clarity(), new Clarity()]);

        $result = $validator->validate(new Clarity(), $deck);

        $this->assertNull($result);
    }

    #[Test]
    public function validate_returns_failure_when_deck_already_has_3_clarity_cards(): void
    {
        $validator = new ClarityDeckValidator();
        $deck = new ImmutableArray(array_fill(0, 3, new Clarity()));

        $result = $validator->validate(new Clarity(), $deck);

        $this->assertNotNull($result);
        $this->assertSame('You can only have 3 clarity cards in your deck', $result->message);
    }
}
