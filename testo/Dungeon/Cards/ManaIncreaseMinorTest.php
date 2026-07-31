<?php

declare(strict_types=1);

namespace Tests\Testo\Dungeon\Cards;

use App\Dungeon\Cards\ManaIncreaseMinor;
use App\Dungeon\Events\PlayerMaxManaIncreased;
use Testo\Assert;
use Testo\Test;
use Tests\Testo\Dungeon\DungeonTest;

#[Test]
final class ManaIncreaseMinorTest extends DungeonTest
{
    public function play_increases_max_mana_by_25(): void
    {
        $maxManaBefore = $this->dungeon->maxMana;
        $card = new ManaIncreaseMinor();

        $card->play($this->dungeon);

        Assert::same($this->dungeon->maxMana, $maxManaBefore + 25);
        $this->eventBus->assertDispatched(PlayerMaxManaIncreased::class, function (PlayerMaxManaIncreased $event) {
            Assert::same($event->amount, 25);
        });
    }
}
