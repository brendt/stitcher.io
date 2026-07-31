<?php

declare(strict_types=1);

namespace Tests\Testo\Dungeon\Cards;

use App\Dungeon\Cards\ManaIncreaseMajor;
use App\Dungeon\Events\PlayerMaxManaIncreased;
use Testo\Assert;
use Testo\Test;
use Tests\Testo\Dungeon\DungeonTest;

#[Test]
final class ManaIncreaseMajorTest extends DungeonTest
{
    public function play_increases_max_mana_by_50(): void
    {
        $maxManaBefore = $this->dungeon->maxMana;
        $card = new ManaIncreaseMajor();

        $card->play($this->dungeon);

        Assert::same($this->dungeon->maxMana, $maxManaBefore + 50);
        $this->eventBus->assertDispatched(PlayerMaxManaIncreased::class, function (PlayerMaxManaIncreased $event) {
            Assert::same($event->amount, 50);
        });
    }
}
