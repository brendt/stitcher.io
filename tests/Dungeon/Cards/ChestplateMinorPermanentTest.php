<?php

namespace Tests\Dungeon\Cards;

use App\Dungeon\Events\PlayerMoved;
use App\Dungeon\Point;
use Tests\Dungeon\DungeonTest;

use App\Dungeon\Cards\ChestplateMinorPermanent;
use App\Dungeon\Events\CardUpdated;
use App\Dungeon\Events\PlayerHealthDecreased;
use App\Dungeon\Events\PlayerHealthIncreased;
use PHPUnit\Framework\Attributes\Test;

final class ChestplateMinorPermanentTest extends DungeonTest
{
    #[Test]
    public function handle_restores_up_to_5_health_on_damage(): void
    {
        $card = new ChestplateMinorPermanent();
        $this->dungeon->health = 70;

        $card->handle($this->dungeon, new PlayerHealthDecreased(10, 70, null));

        $this->assertSame(75, $this->dungeon->health);
        $this->eventBus->assertDispatched(PlayerHealthIncreased::class, function (PlayerHealthIncreased $event) {
            $this->assertSame(5, $event->amount);
        });
        $this->eventBus->assertDispatched(CardUpdated::class);
    }

    #[Test]
    public function handle_restores_only_the_actual_damage_when_damage_is_less_than_5(): void
    {
        $card = new ChestplateMinorPermanent();
        $this->dungeon->health = 98;

        $card->handle($this->dungeon, new PlayerHealthDecreased(2, 98, null));

        $this->assertSame(100, $this->dungeon->health);
        $this->eventBus->assertDispatched(PlayerHealthIncreased::class, function (PlayerHealthIncreased $event) {
            $this->assertSame(2, $event->amount);
        });
    }

    #[Test]
    public function handle_ignores_unrelated_events(): void
    {
        $card = new ChestplateMinorPermanent();
        $this->dungeon->health = 70;

        $card->handle($this->dungeon, new PlayerMoved(
            from: new Point(0, 0),
            to: new Point(1, 0),
        ));

        $this->assertSame(70, $this->dungeon->health);
        $this->eventBus->assertNotDispatched(CardUpdated::class);
    }
}
