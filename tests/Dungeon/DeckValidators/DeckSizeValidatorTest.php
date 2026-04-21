<?php

namespace Tests\Dungeon\DeckValidators;

use App\Dungeon\Cards\HealMinor;
use App\Dungeon\DeckValidators\DeckSizeValidator;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Support\Arr\ImmutableArray;

final class DeckSizeValidatorTest extends TestCase
{
    #[Test]
    public function validate_returns_null_when_deck_has_fewer_than_20_cards(): void
    {
        $validator = new DeckSizeValidator();
        $deck = new ImmutableArray(array_fill(0, 19, new HealMinor()));

        $result = $validator->validate(new HealMinor(), $deck);

        $this->assertNull($result);
    }

    #[Test]
    public function validate_returns_failure_when_deck_has_20_cards(): void
    {
        $validator = new DeckSizeValidator();
        $deck = new ImmutableArray(array_fill(0, 20, new HealMinor()));

        $result = $validator->validate(new HealMinor(), $deck);

        $this->assertNotNull($result);
        $this->assertSame('Your deck is full', $result->message);
    }
}
