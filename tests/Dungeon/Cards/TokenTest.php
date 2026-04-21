<?php

namespace Tests\Dungeon\Cards;

use Tests\Dungeon\DungeonTest;

use App\Dungeon\Cards\Token;
use PHPUnit\Framework\Attributes\Test;

final class TokenTest extends DungeonTest
{
    #[Test]
    public function play_does_nothing(): void
    {
        $card = new Token();

        $card->play($this->dungeon);

        // Token is a META card — it grants a token when purchased from the shop;
        // playing it in-game has no effect.
        $this->assertTrue($this->dungeon->hasEnded === false);
    }
}
