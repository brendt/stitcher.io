<?php

namespace Tests\Dungeon\DeckValidators;

use App\Dungeon\Cards\ChestplateMajorPermanent;
use App\Dungeon\Cards\ChestplateMinorPermanent;
use App\Dungeon\Cards\HealMinor;
use App\Dungeon\Cards\ManaPerMovePermanent;
use App\Dungeon\DeckValidators\PermanentCardDeckValidator;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Support\Arr\ImmutableArray;

final class PermanentCardDeckValidatorTest extends TestCase
{
    #[Test]
    public function validate_returns_null_for_non_permanent_card(): void
    {
        $validator = new PermanentCardDeckValidator();
        $deck = new ImmutableArray([
            new ChestplateMinorPermanent(),
            new ChestplateMinorPermanent(),
            new ChestplateMinorPermanent(),
        ]);

        $result = $validator->validate(new HealMinor(), $deck);

        $this->assertNull($result);
    }

    #[Test]
    public function validate_returns_null_when_deck_has_fewer_than_3_permanent_cards(): void
    {
        $validator = new PermanentCardDeckValidator();
        $deck = new ImmutableArray([
            new ChestplateMinorPermanent(),
            new ChestplateMajorPermanent(),
        ]);

        $result = $validator->validate(new ManaPerMovePermanent(), $deck);

        $this->assertNull($result);
    }

    #[Test]
    public function validate_returns_failure_when_deck_already_has_3_permanent_cards(): void
    {
        $validator = new PermanentCardDeckValidator();
        $deck = new ImmutableArray([
            new ChestplateMinorPermanent(),
            new ChestplateMajorPermanent(),
            new ManaPerMovePermanent(),
        ]);

        $result = $validator->validate(new ChestplateMinorPermanent(), $deck);

        $this->assertNotNull($result);
        $this->assertSame('You can only have 3 permanent cards in your deck', $result->message);
    }
}
