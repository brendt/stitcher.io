<?php

declare(strict_types=1);

namespace Tests\Testo\Dungeon\Cards;

use App\Dungeon\Cards\ChestplateMinorPermanent;
use App\Dungeon\Events\CardUpdated;
use App\Dungeon\Events\PlayerHealthDecreased;
use App\Dungeon\Events\PlayerHealthIncreased;
use App\Dungeon\Events\PlayerMoved;
use App\Dungeon\Point;
use Testo\Assert;
use Testo\Test;
use Tests\Testo\Dungeon\DungeonTest;

#[Test]
final class ChestplateMinorPermanentTest extends DungeonTest
{
    public function handle_restores_up_to_5_health_on_damage(): void
    {
        $card = new ChestplateMinorPermanent();
        $this->dungeon->health = 70;

        $card->handle($this->dungeon, new PlayerHealthDecreased(10, 70, null));

        Assert::same($this->dungeon->health, 75);
        $this->eventBus->assertDispatched(PlayerHealthIncreased::class, function (PlayerHealthIncreased $event) {
            Assert::same($event->amount, 5);
        });
        $this->eventBus->assertDispatched(CardUpdated::class);
    }

    public function handle_restores_only_the_actual_damage_when_damage_is_less_than_5(): void
    {
        $card = new ChestplateMinorPermanent();
        $this->dungeon->health = 98;

        $card->handle($this->dungeon, new PlayerHealthDecreased(2, 98, null));

        Assert::same($this->dungeon->health, 100);
        $this->eventBus->assertDispatched(PlayerHealthIncreased::class, function (PlayerHealthIncreased $event) {
            Assert::same($event->amount, 2);
        });
    }

    public function handle_ignores_unrelated_events(): void
    {
        $card = new ChestplateMinorPermanent();
        $this->dungeon->health = 70;

        $card->handle($this->dungeon, new PlayerMoved(
            from: new Point(0, 0),
            to: new Point(1, 0),
        ));

        Assert::same($this->dungeon->health, 70);
        $this->eventBus->assertNotDispatched(CardUpdated::class);
    }
}
