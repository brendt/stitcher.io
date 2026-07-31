<?php

declare(strict_types=1);

namespace Tests\Testo\Dungeon\Cards;

use App\Dungeon\Cards\VictoryPoint;
use Testo\Assert;
use Testo\Test;
use Tests\Testo\Dungeon\DungeonTest;

#[Test]
final class VictoryPointTest extends DungeonTest
{
    public function play_does_nothing(): void
    {
        $card = new VictoryPoint();

        $card->play($this->dungeon);

        // VictoryPoint is a META card — it grants a VP when purchased from the shop;
        // playing it in-game has no effect.
        Assert::true($this->dungeon->hasEnded === false);
    }
}
