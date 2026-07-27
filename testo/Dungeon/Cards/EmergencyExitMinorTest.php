<?php

declare(strict_types=1);

namespace Tests\Testo\Dungeon\Cards;

use App\Dungeon\Cards\EmergencyExitMinor;
use App\Dungeon\Events\PlayerExited;
use Testo\Assert;
use Testo\Test;
use Tests\Testo\Dungeon\DungeonTest;

#[Test]
final class EmergencyExitMinorTest extends DungeonTest
{
    public function play_exits_with_30_percent_of_coins(): void
    {
        $this->dungeon->coins = 100;
        $card = new EmergencyExitMinor();

        $card->play($this->dungeon);

        Assert::same($this->dungeon->coins, 30);
        Assert::true($this->dungeon->hasEnded);
        $this->eventBus->assertDispatched(PlayerExited::class, function (PlayerExited $event) {
            Assert::same($event->coins, 30);
        });
    }

    public function play_exits_without_requiring_origin_tile(): void
    {
        $this->dungeon->coins = 0;
        $card = new EmergencyExitMinor();

        $card->play($this->dungeon);

        Assert::true($this->dungeon->hasEnded);
    }
}
