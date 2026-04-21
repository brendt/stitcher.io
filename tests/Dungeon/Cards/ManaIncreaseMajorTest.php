<?php

namespace Tests\Dungeon\Cards;

use Tests\Dungeon\DungeonTest;

use App\Dungeon\Cards\ManaIncreaseMajor;
use App\Dungeon\Events\PlayerMaxManaIncreased;
use PHPUnit\Framework\Attributes\Test;

final class ManaIncreaseMajorTest extends DungeonTest
{
    #[Test]
    public function play_increases_max_mana_by_50(): void
    {
        $maxManaBefore = $this->dungeon->maxMana;
        $card = new ManaIncreaseMajor();

        $card->play($this->dungeon);

        $this->assertSame($maxManaBefore + 50, $this->dungeon->maxMana);
        $this->eventBus->assertDispatched(PlayerMaxManaIncreased::class, function (PlayerMaxManaIncreased $event) {
            $this->assertSame(50, $event->amount);
        });
    }
}
