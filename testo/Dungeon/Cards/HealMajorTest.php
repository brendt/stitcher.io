<?php

declare(strict_types=1);

namespace Tests\Testo\Dungeon\Cards;

use App\Dungeon\Cards\HealMajor;
use App\Dungeon\Events\PlayerHealthIncreased;
use Testo\Assert;
use Testo\Test;
use Tests\Testo\Dungeon\DungeonTest;

#[Test]
final class HealMajorTest extends DungeonTest
{
    public function play_increases_health_by_50(): void
    {
        $this->dungeon->health = 40;
        $card = new HealMajor();

        $card->play($this->dungeon);

        Assert::same($this->dungeon->health, 90);
        $this->eventBus->assertDispatched(PlayerHealthIncreased::class, function (PlayerHealthIncreased $event) {
            Assert::same($event->amount, 50);
        });
    }

    public function play_is_capped_at_max_health(): void
    {
        $this->dungeon->health = 80; // max is 100
        $card = new HealMajor();

        $card->play($this->dungeon);

        Assert::same($this->dungeon->health, 100);
    }
}
