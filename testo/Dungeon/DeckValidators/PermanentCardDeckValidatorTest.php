<?php

declare(strict_types=1);

namespace Tests\Testo\Dungeon\DeckValidators;

use App\Dungeon\Cards\ChestplateMajorPermanent;
use App\Dungeon\Cards\ChestplateMinorPermanent;
use App\Dungeon\Cards\HealMinor;
use App\Dungeon\Cards\ManaPerMovePermanent;
use App\Dungeon\DeckValidators\PermanentCardDeckValidator;
use Tempest\Support\Arr\ImmutableArray;
use Testo\Assert;
use Testo\Test;

#[Test]
final class PermanentCardDeckValidatorTest
{
    public function validate_returns_null_for_non_permanent_card(): void
    {
        $validator = new PermanentCardDeckValidator();
        $deck = new ImmutableArray([
            new ChestplateMinorPermanent(),
            new ChestplateMinorPermanent(),
            new ChestplateMinorPermanent(),
        ]);

        $result = $validator->validate(new HealMinor(), $deck);

        Assert::null($result);
    }

    public function validate_returns_null_when_deck_has_fewer_than_3_permanent_cards(): void
    {
        $validator = new PermanentCardDeckValidator();
        $deck = new ImmutableArray([
            new ChestplateMinorPermanent(),
            new ChestplateMajorPermanent(),
        ]);

        $result = $validator->validate(new ManaPerMovePermanent(), $deck);

        Assert::null($result);
    }

    public function validate_returns_failure_when_deck_already_has_3_permanent_cards(): void
    {
        $validator = new PermanentCardDeckValidator();
        $deck = new ImmutableArray([
            new ChestplateMinorPermanent(),
            new ChestplateMajorPermanent(),
            new ManaPerMovePermanent(),
        ]);

        $result = $validator->validate(new ChestplateMinorPermanent(), $deck);

        Assert::notNull($result);
        Assert::same($result->message, 'You can only have 3 permanent cards in your deck');
    }
}
