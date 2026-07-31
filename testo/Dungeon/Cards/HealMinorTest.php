<?php

declare(strict_types=1);

namespace Tests\Testo\Dungeon\Cards;

use App\Dungeon\Cards\HealMinor;
use App\Dungeon\Events\PlayerHealthIncreased;
use Testo\Assert;
use Testo\Test;
use Tests\Testo\Dungeon\DungeonTest;

#[Test]
final class HealMinorTest extends DungeonTest
{
    public function play_increases_health_by_25(): void
    {
        $this->dungeon->health = 50;
        $card = new HealMinor();

        $card->play($this->dungeon);

        Assert::same($this->dungeon->health, 75);
        $this->eventBus->assertDispatched(PlayerHealthIncreased::class, function (PlayerHealthIncreased $event) {
            Assert::same($event->amount, 25);
        });
    }

    public function play_is_capped_at_max_health(): void
    {
        $this->dungeon->health = 90;
        $card = new HealMinor();

        $card->play($this->dungeon);

        Assert::same($this->dungeon->health, 100);
    }
}
