<?php

namespace Tests\Dungeon\Cards;

use App\Dungeon\Cards\VictoryPoint;
use Tempest\Testing\Test;
use Tests\Dungeon\DungeonTest;

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
