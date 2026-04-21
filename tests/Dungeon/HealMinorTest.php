<?php

namespace Tests\Dungeon;

use App\Dungeon\Cards\HealMinor;
use App\Dungeon\Events\PlayerHealthIncreased;
use PHPUnit\Framework\Attributes\Test;

final class HealMinorTest extends DungeonTest
{
    #[Test]
    public function play_increases_health_by_25(): void
    {
        $this->dungeon->health = 50;
        $card = new HealMinor();

        $card->play($this->dungeon);

        $this->assertSame(75, $this->dungeon->health);
        $this->eventBus->assertDispatched(PlayerHealthIncreased::class, function (PlayerHealthIncreased $event) {
            $this->assertSame(25, $event->amount);
        });
    }

    #[Test]
    public function play_is_capped_at_max_health(): void
    {
        $this->dungeon->health = 90;
        $card = new HealMinor();

        $card->play($this->dungeon);

        $this->assertSame(100, $this->dungeon->health);
    }
}
