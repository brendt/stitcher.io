<?php

namespace Tests\Dungeon\Cards;

use Tests\Dungeon\DungeonTest;

use App\Dungeon\Cards\ManaIncreaseMinor;
use App\Dungeon\Events\PlayerMaxManaIncreased;
use PHPUnit\Framework\Attributes\Test;

final class ManaIncreaseMinorTest extends DungeonTest
{
    #[Test]
    public function play_increases_max_mana_by_25(): void
    {
        $maxManaBefore = $this->dungeon->maxMana;
        $card = new ManaIncreaseMinor();

        $card->play($this->dungeon);

        $this->assertSame($maxManaBefore + 25, $this->dungeon->maxMana);
        $this->eventBus->assertDispatched(PlayerMaxManaIncreased::class, function (PlayerMaxManaIncreased $event) {
            $this->assertSame(25, $event->amount);
        });
    }
}
