<?php

declare(strict_types=1);

namespace Tests\Testo\Dungeon\Cards;

use App\Dungeon\Cards\Token;
use Testo\Assert;
use Testo\Test;
use Tests\Testo\Dungeon\DungeonTest;

#[Test]
final class TokenTest extends DungeonTest
{
    public function play_does_nothing(): void
    {
        $card = new Token();

        $card->play($this->dungeon);

        // Token is a META card — it grants a token when purchased from the shop;
        // playing it in-game has no effect.
        Assert::true($this->dungeon->hasEnded === false);
    }
}
