<?php

namespace Tests\Dungeon;

use App\Dungeon\Cards\VictoryPoint;
use PHPUnit\Framework\Attributes\Test;

final class VictoryPointTest extends DungeonTest
{
    #[Test]
    public function play_does_nothing(): void
    {
        $card = new VictoryPoint();

        $card->play($this->dungeon);

        // VictoryPoint is a META card — it grants a VP when purchased from the shop;
        // playing it in-game has no effect.
        $this->assertTrue($this->dungeon->hasEnded === false);
    }
}
