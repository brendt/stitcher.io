<?php

namespace Tests\Dungeon\Cards;

use Tests\Dungeon\DungeonTest;

use App\Dungeon\Cards\HealthIncreaseMinor;
use App\Dungeon\Events\PlayerHealthIncreased;
use App\Dungeon\Events\PlayerMaxHealthIncreased;
use PHPUnit\Framework\Attributes\Test;

final class HealthIncreaseMinorTest extends DungeonTest
{
    #[Test]
    public function play_increases_max_health_by_25(): void
    {
        $maxHealthBefore = $this->dungeon->maxHealth;
        $card = new HealthIncreaseMinor();

        $card->play($this->dungeon);

        $this->assertSame($maxHealthBefore + 25, $this->dungeon->maxHealth);
        $this->eventBus->assertDispatched(PlayerMaxHealthIncreased::class, function (PlayerMaxHealthIncreased $event) {
            $this->assertSame(25, $event->amount);
        });
    }

    #[Test]
    public function play_increases_current_health_by_15(): void
    {
        $this->dungeon->health = 10;
        $this->dungeon->maxHealth = 200; // room to grow
        $card = new HealthIncreaseMinor();

        $card->play($this->dungeon);

        $this->assertSame(25, $this->dungeon->health);
        $this->eventBus->assertDispatched(PlayerHealthIncreased::class, function (PlayerHealthIncreased $event) {
            $this->assertSame(15, $event->amount);
        });
    }
}
