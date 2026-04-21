<?php

namespace Tests\Dungeon;

use App\Dungeon\Cards\StabilityMajor;
use App\Dungeon\Events\PlayerStabilityIncreased;
use PHPUnit\Framework\Attributes\Test;

final class StabilityMajorTest extends DungeonTest
{
    #[Test]
    public function play_increases_stability_by_50(): void
    {
        $this->dungeon->stability = 30;
        $card = new StabilityMajor();

        $card->play($this->dungeon);

        $this->assertSame(80, $this->dungeon->stability);
        $this->eventBus->assertDispatched(PlayerStabilityIncreased::class, function (PlayerStabilityIncreased $event) {
            $this->assertSame(50, $event->amount);
        });
    }

    #[Test]
    public function play_is_capped_at_max_stability(): void
    {
        $this->dungeon->stability = 80;
        $card = new StabilityMajor();

        $card->play($this->dungeon);

        $this->assertSame(100, $this->dungeon->stability);
    }
}
