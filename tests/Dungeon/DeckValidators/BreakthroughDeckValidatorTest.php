<?php

namespace Tests\Dungeon\DeckValidators;

use App\Dungeon\Cards\BreakthroughMajor;
use App\Dungeon\Cards\BreakthroughMinor;
use App\Dungeon\Cards\HealMinor;
use App\Dungeon\DeckValidators\BreakthroughDeckValidator;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Support\Arr\ImmutableArray;

final class BreakthroughDeckValidatorTest extends TestCase
{
    #[Test]
    public function validate_returns_null_for_non_breakthrough_card(): void
    {
        $validator = new BreakthroughDeckValidator();
        $deck = new ImmutableArray(array_fill(0, 5, new BreakthroughMinor()));

        $result = $validator->validate(new HealMinor(), $deck);

        $this->assertNull($result);
    }

    #[Test]
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

        $this->assertNull($result);
    }

    #[Test]
    public function validate_returns_failure_when_deck_already_has_5_breakthrough_cards(): void
    {
        $validator = new BreakthroughDeckValidator();
        $deck = new ImmutableArray(array_fill(0, 5, new BreakthroughMinor()));

        $result = $validator->validate(new BreakthroughMajor(), $deck);

        $this->assertNotNull($result);
        $this->assertSame('You can only have 5 breakthrough cards in your deck', $result->message);
    }
}
