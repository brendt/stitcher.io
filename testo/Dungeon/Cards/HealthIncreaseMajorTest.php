<?php

declare(strict_types=1);

namespace Tests\Testo\Dungeon\Cards;

use App\Dungeon\Cards\HealthIncreaseMajor;
use App\Dungeon\Events\PlayerHealthIncreased;
use App\Dungeon\Events\PlayerMaxHealthIncreased;
use Testo\Assert;
use Testo\Test;
use Tests\Testo\Dungeon\DungeonTest;

#[Test]
final class HealthIncreaseMajorTest extends DungeonTest
{
    public function play_increases_max_health_by_50(): void
    {
        $maxHealthBefore = $this->dungeon->maxHealth;
        $card = new HealthIncreaseMajor();

        $card->play($this->dungeon);

        Assert::same($this->dungeon->maxHealth, $maxHealthBefore + 50);
        $this->eventBus->assertDispatched(PlayerMaxHealthIncreased::class, function (PlayerMaxHealthIncreased $event) {
            Assert::same($event->amount, 50);
        });
    }

    public function play_increases_current_health_by_40(): void
    {
        $this->dungeon->health = 10;
        $this->dungeon->maxHealth = 200; // room to grow
        $card = new HealthIncreaseMajor();

        $card->play($this->dungeon);

        Assert::same($this->dungeon->health, 50);
        $this->eventBus->assertDispatched(PlayerHealthIncreased::class, function (PlayerHealthIncreased $event) {
            Assert::same($event->amount, 40);
        });
    }
}
