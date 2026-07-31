<?php

declare(strict_types=1);

namespace Tests\Testo\Dungeon\Cards;

use App\Dungeon\Cards\EmergencyExitMajor;
use App\Dungeon\Events\PlayerExited;
use Testo\Assert;
use Testo\Test;
use Tests\Testo\Dungeon\DungeonTest;

#[Test]
final class EmergencyExitMajorTest extends DungeonTest
{
    public function play_exits_with_65_percent_of_coins(): void
    {
        $this->dungeon->coins = 100;
        $card = new EmergencyExitMajor();

        $card->play($this->dungeon);

        Assert::same($this->dungeon->coins, 65);
        Assert::true($this->dungeon->hasEnded);
        $this->eventBus->assertDispatched(PlayerExited::class, function (PlayerExited $event) {
            Assert::same($event->coins, 65);
        });
    }

    public function play_exits_without_requiring_origin_tile(): void
    {
        $this->dungeon->coins = 0;
        $card = new EmergencyExitMajor();

        $card->play($this->dungeon);

        Assert::true($this->dungeon->hasEnded);
    }
}
