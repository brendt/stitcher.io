<?php

declare(strict_types=1);

namespace Tests\Testo\Dungeon\Cards;

use App\Dungeon\Cards\HealthIncreaseMinor;
use App\Dungeon\Events\PlayerHealthIncreased;
use App\Dungeon\Events\PlayerMaxHealthIncreased;
use Testo\Assert;
use Testo\Test;
use Tests\Testo\Dungeon\DungeonTest;

#[Test]
final class HealthIncreaseMinorTest extends DungeonTest
{
    public function play_increases_max_health_by_25(): void
    {
        $maxHealthBefore = $this->dungeon->maxHealth;
        $card = new HealthIncreaseMinor();

        $card->play($this->dungeon);

        Assert::same($this->dungeon->maxHealth, $maxHealthBefore + 25);
        $this->eventBus->assertDispatched(PlayerMaxHealthIncreased::class, function (PlayerMaxHealthIncreased $event) {
            Assert::same($event->amount, 25);
        });
    }

    public function play_increases_current_health_by_15(): void
    {
        $this->dungeon->health = 10;
        $this->dungeon->maxHealth = 200; // room to grow
        $card = new HealthIncreaseMinor();

        $card->play($this->dungeon);

        Assert::same($this->dungeon->health, 25);
        $this->eventBus->assertDispatched(PlayerHealthIncreased::class, function (PlayerHealthIncreased $event) {
            Assert::same($event->amount, 15);
        });
    }
}
