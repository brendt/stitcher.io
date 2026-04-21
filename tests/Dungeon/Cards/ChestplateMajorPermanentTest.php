<?php

namespace Tests\Dungeon\Cards;

use Tests\Dungeon\DungeonTest;

use App\Dungeon\Cards\ChestplateMajorPermanent;
use App\Dungeon\Events\CardUpdated;
use App\Dungeon\Events\PlayerHealthDecreased;
use App\Dungeon\Events\PlayerHealthIncreased;
use PHPUnit\Framework\Attributes\Test;

final class ChestplateMajorPermanentTest extends DungeonTest
{
    #[Test]
    public function handle_restores_up_to_10_health_on_damage(): void
    {
        $card = new ChestplateMajorPermanent();
        $this->dungeon->health = 70;

        $card->handle($this->dungeon, new PlayerHealthDecreased(15, 70, null));

        $this->assertSame(80, $this->dungeon->health);
        $this->eventBus->assertDispatched(PlayerHealthIncreased::class, function (PlayerHealthIncreased $event) {
            $this->assertSame(10, $event->amount);
        });
        $this->eventBus->assertDispatched(CardUpdated::class);
    }

    #[Test]
    public function handle_restores_only_the_actual_damage_when_damage_is_less_than_10(): void
    {
        $card = new ChestplateMajorPermanent();
        $this->dungeon->health = 95;

        $card->handle($this->dungeon, new PlayerHealthDecreased(5, 95, null));

        $this->assertSame(100, $this->dungeon->health);
        $this->eventBus->assertDispatched(PlayerHealthIncreased::class, function (PlayerHealthIncreased $event) {
            $this->assertSame(5, $event->amount);
        });
    }

    #[Test]
    public function handle_ignores_unrelated_events(): void
    {
        $card = new ChestplateMajorPermanent();
        $this->dungeon->health = 70;

        $card->handle($this->dungeon, new \App\Dungeon\Events\PlayerMoved(
            from: new \App\Dungeon\Point(0, 0),
            to: new \App\Dungeon\Point(1, 0),
        ));

        $this->assertSame(70, $this->dungeon->health);
        $this->eventBus->assertNotDispatched(CardUpdated::class);
    }
}
