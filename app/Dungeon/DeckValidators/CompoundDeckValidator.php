<?php

namespace App\Dungeon\DeckValidators;

use App\Dungeon\Card;
use App\Dungeon\DeckValidationFailed;
use App\Dungeon\DeckValidator;
use Tempest\Container\Autowire;
use Tempest\Container\Singleton;
use Tempest\Support\Arr\ImmutableArray;

#[Singleton, Autowire]
final class CompoundDeckValidator implements DeckValidator
{
    /** @var DeckValidator[] $validators */
    private array $validators;

    public function addValidator(DeckValidator $validator): void
    {
        $this->validators[] = $validator;
    }

    public function validate(Card $card, ImmutableArray $deck): ?DeckValidationFailed
    {
        foreach ($this->validators as $validator) {
            $result = $validator->validate($card, $deck);

            if ($result !== null) {
                return $result;
            }
        }

        return null;
    }
}