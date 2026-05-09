<?php

namespace Tests\Dungeon\DeckValidators;

use App\Dungeon\Card;
use App\Dungeon\DeckValidationFailed;
use App\Dungeon\DeckValidator;
use App\Dungeon\DeckValidators\CompoundDeckValidator;
use App\Dungeon\Cards\HealMinor;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Support\Arr\ImmutableArray;

final class CompoundDeckValidatorTest extends TestCase
{
    #[Test]
    public function validate_returns_null_when_all_validators_pass(): void
    {
        $compound = new CompoundDeckValidator();
        $compound->addValidator(new class implements DeckValidator {
            public function validate(Card $card, ImmutableArray $deck): ?DeckValidationFailed
            {
                return null;
            }
        });
        $compound->addValidator(new class implements DeckValidator {
            public function validate(Card $card, ImmutableArray $deck): ?DeckValidationFailed
            {
                return null;
            }
        });

        $result = $compound->validate(new HealMinor(), new ImmutableArray([]));

        $this->assertNull($result);
    }

    #[Test]
    public function validate_returns_first_failure(): void
    {
        $compound = new CompoundDeckValidator();
        $compound->addValidator(new class implements DeckValidator {
            public function validate(Card $card, ImmutableArray $deck): ?DeckValidationFailed
            {
                return new DeckValidationFailed('First failure');
            }
        });
        $compound->addValidator(new class implements DeckValidator {
            public function validate(Card $card, ImmutableArray $deck): ?DeckValidationFailed
            {
                return new DeckValidationFailed('Second failure');
            }
        });

        $result = $compound->validate(new HealMinor(), new ImmutableArray([]));

        $this->assertNotNull($result);
        $this->assertSame('First failure', $result->message);
    }

    #[Test]
    public function validate_stops_at_first_failure_and_skips_remaining_validators(): void
    {
        $compound = new CompoundDeckValidator();
        $compound->addValidator(new class implements DeckValidator {
            public function validate(Card $card, ImmutableArray $deck): ?DeckValidationFailed
            {
                return null;
            }
        });
        $compound->addValidator(new class implements DeckValidator {
            public function validate(Card $card, ImmutableArray $deck): ?DeckValidationFailed
            {
                return new DeckValidationFailed('Blocking failure');
            }
        });
        $spy = new class implements DeckValidator {
            public bool $reached = false;

            public function validate(Card $card, ImmutableArray $deck): ?DeckValidationFailed
            {
                $this->reached = true;

                return null;
            }
        };
        $compound->addValidator($spy);

        $compound->validate(new HealMinor(), new ImmutableArray([]));

        $this->assertFalse($spy->reached);
    }
}
