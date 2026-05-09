<?php

namespace Tests\Dungeon\DeckValidators;

use App\Dungeon\Cards\HealMinor;
use App\Dungeon\Cards\StabilityMajor;
use App\Dungeon\Cards\StabilityMinor;
use App\Dungeon\DeckValidators\StabilityDeckValidator;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Support\Arr\ImmutableArray;

final class StabilityDeckValidatorTest extends TestCase
{
    #[Test]
    public function validate_returns_null_for_non_stability_card(): void
    {
        $validator = new StabilityDeckValidator();
        $deck = new ImmutableArray(array_fill(0, 7, new StabilityMinor()));

        $result = $validator->validate(new HealMinor(), $deck);

        $this->assertNull($result);
    }

    #[Test]
    public function validate_returns_null_when_deck_has_fewer_than_7_stability_cards(): void
    {
        $validator = new StabilityDeckValidator();
        $deck = new ImmutableArray(array_fill(0, 6, new StabilityMinor()));

        $result = $validator->validate(new StabilityMajor(), $deck);

        $this->assertNull($result);
    }

    #[Test]
    public function validate_returns_failure_when_deck_already_has_7_stability_cards(): void
    {
        $validator = new StabilityDeckValidator();
        $deck = new ImmutableArray(array_fill(0, 7, new StabilityMinor()));

        $result = $validator->validate(new StabilityMajor(), $deck);

        $this->assertNotNull($result);
        $this->assertSame('You can only have 7 stability cards in your deck', $result->message);
    }
}
