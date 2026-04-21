<?php

namespace Tests\Dungeon;

use App\Dungeon\Cards\Clarity;
use App\Dungeon\Events\PlayerStabilityIncreased;
use App\Dungeon\Events\VisibilityChanged;
use PHPUnit\Framework\Attributes\Test;

final class ClarityTest extends DungeonTest
{
    #[Test]
    public function play_increases_visibility_radius_by_one(): void
    {
        $radiusBefore = $this->dungeon->visibilityRadius;
        $card = new Clarity();

        $card->play($this->dungeon);

        $this->assertSame($radiusBefore + 1, $this->dungeon->visibilityRadius);
        $this->eventBus->assertDispatched(VisibilityChanged::class, function (VisibilityChanged $event) use ($radiusBefore) {
            $this->assertSame($radiusBefore + 1, $event->visibilityRadius);
        });
    }

    #[Test]
    public function play_increases_stability_by_20(): void
    {
        $this->dungeon->stability = 50;
        $card = new Clarity();

        $card->play($this->dungeon);

        $this->assertSame(70, $this->dungeon->stability);
        $this->eventBus->assertDispatched(PlayerStabilityIncreased::class, function (PlayerStabilityIncreased $event) {
            $this->assertSame(20, $event->amount);
        });
    }
}
