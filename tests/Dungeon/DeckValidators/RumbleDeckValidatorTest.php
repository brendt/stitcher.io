<?php

namespace Tests\Dungeon\DeckValidators;

use App\Dungeon\Cards\HealMinor;
use App\Dungeon\Cards\RumbleMajor;
use App\Dungeon\Cards\RumbleMinor;
use App\Dungeon\DeckValidators\RumbleDeckValidator;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Support\Arr\ImmutableArray;

final class RumbleDeckValidatorTest extends TestCase
{
    #[Test]
    public function validate_returns_null_for_non_rumble_card(): void
    {
        $validator = new RumbleDeckValidator();
        $deck = new ImmutableArray(array_fill(0, 5, new RumbleMinor()));

        $result = $validator->validate(new HealMinor(), $deck);

        $this->assertNull($result);
    }

    #[Test]
    public function validate_returns_null_for_rumble_major(): void
    {
        // RumbleDeckValidator only checks RumbleMinor; RumbleMajor is not restricted
        $validator = new RumbleDeckValidator();
        $deck = new ImmutableArray(array_fill(0, 5, new RumbleMinor()));

        $result = $validator->validate(new RumbleMajor(), $deck);

        $this->assertNull($result);
    }

    #[Test]
    public function validate_returns_null_when_deck_has_fewer_than_5_rumble_minor_cards(): void
    {
        $validator = new RumbleDeckValidator();
        $deck = new ImmutableArray(array_fill(0, 4, new RumbleMinor()));

        $result = $validator->validate(new RumbleMinor(), $deck);

        $this->assertNull($result);
    }

    #[Test]
    public function validate_returns_failure_when_deck_already_has_5_rumble_minor_cards(): void
    {
        $validator = new RumbleDeckValidator();
        $deck = new ImmutableArray(array_fill(0, 5, new RumbleMinor()));

        $result = $validator->validate(new RumbleMinor(), $deck);

        $this->assertNotNull($result);
        $this->assertSame('You can only have 5 rumble cards in your deck', $result->message);
    }
}
