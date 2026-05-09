<?php

namespace Tests\Dungeon\Cards;

use Tests\Dungeon\DungeonTest;

use App\Dungeon\Cards\StabilityMinor;
use App\Dungeon\Events\PlayerStabilityIncreased;
use PHPUnit\Framework\Attributes\Test;

final class StabilityMinorTest extends DungeonTest
{
    #[Test]
    public function play_increases_stability_by_25(): void
    {
        $this->dungeon->stability = 30;
        $card = new StabilityMinor();

        $card->play($this->dungeon);

        $this->assertSame(55, $this->dungeon->stability);
        $this->eventBus->assertDispatched(PlayerStabilityIncreased::class, function (PlayerStabilityIncreased $event) {
            $this->assertSame(25, $event->amount);
        });
    }

    #[Test]
    public function play_is_capped_at_max_stability(): void
    {
        $this->dungeon->stability = 90;
        $card = new StabilityMinor();

        $card->play($this->dungeon);

        $this->assertSame(100, $this->dungeon->stability);
    }
}
