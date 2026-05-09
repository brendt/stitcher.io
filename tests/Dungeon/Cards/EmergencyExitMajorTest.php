<?php

namespace Tests\Dungeon\Cards;

use Tests\Dungeon\DungeonTest;

use App\Dungeon\Cards\EmergencyExitMajor;
use App\Dungeon\Events\PlayerExited;
use PHPUnit\Framework\Attributes\Test;

final class EmergencyExitMajorTest extends DungeonTest
{
    #[Test]
    public function play_exits_with_65_percent_of_coins(): void
    {
        $this->dungeon->coins = 100;
        $card = new EmergencyExitMajor();

        $card->play($this->dungeon);

        $this->assertSame(65, $this->dungeon->coins);
        $this->assertTrue($this->dungeon->hasEnded);
        $this->eventBus->assertDispatched(PlayerExited::class, function (PlayerExited $event) {
            $this->assertSame(65, $event->coins);
        });
    }

    #[Test]
    public function play_exits_without_requiring_origin_tile(): void
    {
        $this->dungeon->coins = 0;
        $card = new EmergencyExitMajor();

        $card->play($this->dungeon);

        $this->assertTrue($this->dungeon->hasEnded);
    }
}
