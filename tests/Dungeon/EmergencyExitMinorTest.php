<?php

namespace Tests\Dungeon;

use App\Dungeon\Cards\EmergencyExitMinor;
use App\Dungeon\Events\PlayerExited;
use PHPUnit\Framework\Attributes\Test;

final class EmergencyExitMinorTest extends DungeonTest
{
    #[Test]
    public function play_exits_with_30_percent_of_coins(): void
    {
        $this->dungeon->coins = 100;
        $card = new EmergencyExitMinor();

        $card->play($this->dungeon);

        $this->assertSame(30, $this->dungeon->coins);
        $this->assertTrue($this->dungeon->hasEnded);
        $this->eventBus->assertDispatched(PlayerExited::class, function (PlayerExited $event) {
            $this->assertSame(30, $event->coins);
        });
    }

    #[Test]
    public function play_exits_without_requiring_origin_tile(): void
    {
        $this->dungeon->coins = 0;
        $card = new EmergencyExitMinor();

        $card->play($this->dungeon);

        $this->assertTrue($this->dungeon->hasEnded);
    }
}
