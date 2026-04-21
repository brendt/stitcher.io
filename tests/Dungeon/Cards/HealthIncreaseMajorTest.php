<?php

namespace Tests\Dungeon\Cards;

use Tests\Dungeon\DungeonTest;

use App\Dungeon\Cards\HealthIncreaseMajor;
use App\Dungeon\Events\PlayerHealthIncreased;
use App\Dungeon\Events\PlayerMaxHealthIncreased;
use PHPUnit\Framework\Attributes\Test;

final class HealthIncreaseMajorTest extends DungeonTest
{
    #[Test]
    public function play_increases_max_health_by_50(): void
    {
        $maxHealthBefore = $this->dungeon->maxHealth;
        $card = new HealthIncreaseMajor();

        $card->play($this->dungeon);

        $this->assertSame($maxHealthBefore + 50, $this->dungeon->maxHealth);
        $this->eventBus->assertDispatched(PlayerMaxHealthIncreased::class, function (PlayerMaxHealthIncreased $event) {
            $this->assertSame(50, $event->amount);
        });
    }

    #[Test]
    public function play_increases_current_health_by_40(): void
    {
        $this->dungeon->health = 10;
        $this->dungeon->maxHealth = 200; // room to grow
        $card = new HealthIncreaseMajor();

        $card->play($this->dungeon);

        $this->assertSame(50, $this->dungeon->health);
        $this->eventBus->assertDispatched(PlayerHealthIncreased::class, function (PlayerHealthIncreased $event) {
            $this->assertSame(40, $event->amount);
        });
    }
}
