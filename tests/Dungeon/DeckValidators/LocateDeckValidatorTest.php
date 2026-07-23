<?php

namespace Tests\Dungeon\DeckValidators;

use App\Dungeon\Cards\HealMinor;
use App\Dungeon\Cards\LocateHealthAltar;
use App\Dungeon\Cards\LocateManaAltar;
use App\Dungeon\Cards\LocateStabilityAltar;
use App\Dungeon\DeckValidators\LocateDeckValidator;
use Tempest\Support\Arr\ImmutableArray;
use Tempest\Testing\Test;
use Tests\Dungeon\DungeonAssertions;

final class LocateDeckValidatorTest
{
    use DungeonAssertions;

    #[Test]
    public function validate_returns_null_for_non_locate_card(): void
    {
        $validator = new LocateDeckValidator();
        $deck = new ImmutableArray(array_fill(0, 3, new LocateHealthAltar()));

        $result = $validator->validate(new HealMinor(), $deck);

        $this->assertNull($result);
    }

    #[Test]
    public function validate_returns_null_when_deck_has_fewer_than_3_locate_cards(): void
    {
        $validator = new LocateDeckValidator();
        $deck = new ImmutableArray([
            new LocateHealthAltar(),
            new LocateManaAltar(),
        ]);

        $result = $validator->validate(new LocateStabilityAltar(), $deck);

        $this->assertNull($result);
    }

    #[Test]
    public function validate_returns_failure_when_deck_already_has_3_locate_cards(): void
    {
        $validator = new LocateDeckValidator();
        $deck = new ImmutableArray([
            new LocateHealthAltar(),
            new LocateManaAltar(),
            new LocateStabilityAltar(),
        ]);

        $result = $validator->validate(new LocateHealthAltar(), $deck);

        $this->assertNotNull($result);
        $this->assertSame('You can only have 3 locate cards in your deck', $result->message);
    }
}
