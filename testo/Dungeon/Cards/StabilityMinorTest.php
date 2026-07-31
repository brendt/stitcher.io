<?php

declare(strict_types=1);

namespace Tests\Testo\Dungeon\Cards;

use App\Dungeon\Cards\StabilityMinor;
use App\Dungeon\Events\PlayerStabilityIncreased;
use Testo\Assert;
use Testo\Test;
use Tests\Testo\Dungeon\DungeonTest;

#[Test]
final class StabilityMinorTest extends DungeonTest
{
    public function play_increases_stability_by_25(): void
    {
        $this->dungeon->stability = 30;
        $card = new StabilityMinor();

        $card->play($this->dungeon);

        Assert::same($this->dungeon->stability, 55);
        $this->eventBus->assertDispatched(PlayerStabilityIncreased::class, function (PlayerStabilityIncreased $event) {
            Assert::same($event->amount, 25);
        });
    }

    public function play_is_capped_at_max_stability(): void
    {
        $this->dungeon->stability = 90;
        $card = new StabilityMinor();

        $card->play($this->dungeon);

        Assert::same($this->dungeon->stability, 100);
    }
}
