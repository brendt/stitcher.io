<?php

namespace Tests\Dungeon\Cards;

use App\Dungeon\Cards\VictoryPoint;
use PHPUnit\Framework\Attributes\Test;
use Tests\Dungeon\DungeonTestCase;

final class VictoryPointTestCase extends DungeonTestCase
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
