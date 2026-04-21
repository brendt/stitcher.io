<?php

namespace Tests\Dungeon;

use App\Dungeon\Cards\HealMajor;
use App\Dungeon\Events\PlayerHealthIncreased;
use PHPUnit\Framework\Attributes\Test;

final class HealMajorTest extends DungeonTest
{
    #[Test]
    public function play_increases_health_by_50(): void
    {
        $this->dungeon->health = 40;
        $card = new HealMajor();

        $card->play($this->dungeon);

        $this->assertSame(90, $this->dungeon->health);
        $this->eventBus->assertDispatched(PlayerHealthIncreased::class, function (PlayerHealthIncreased $event) {
            $this->assertSame(50, $event->amount);
        });
    }

    #[Test]
    public function play_is_capped_at_max_health(): void
    {
        $this->dungeon->health = 80; // max is 100
        $card = new HealMajor();

        $card->play($this->dungeon);

        $this->assertSame(100, $this->dungeon->health);
    }
}
