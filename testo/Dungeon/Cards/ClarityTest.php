<?php

declare(strict_types=1);

namespace Tests\Testo\Dungeon\Cards;

use App\Dungeon\Cards\Clarity;
use App\Dungeon\Events\PlayerStabilityIncreased;
use App\Dungeon\Events\VisibilityChanged;
use Testo\Assert;
use Testo\Test;
use Tests\Testo\Dungeon\DungeonTest;

#[Test]
final class ClarityTest extends DungeonTest
{
    public function play_increases_visibility_radius_by_one(): void
    {
        $radiusBefore = $this->dungeon->visibilityRadius;
        $card = new Clarity();

        $card->play($this->dungeon);

        Assert::same($this->dungeon->visibilityRadius, $radiusBefore + 1);
        $this->eventBus->assertDispatched(VisibilityChanged::class, function (VisibilityChanged $event) use ($radiusBefore) {
            Assert::same($event->visibilityRadius, $radiusBefore + 1);
        });
    }

    public function play_increases_stability_by_20(): void
    {
        $this->dungeon->stability = 50;
        $card = new Clarity();

        $card->play($this->dungeon);

        Assert::same($this->dungeon->stability, 70);
        $this->eventBus->assertDispatched(PlayerStabilityIncreased::class, function (PlayerStabilityIncreased $event) {
            Assert::same($event->amount, 20);
        });
    }
}
