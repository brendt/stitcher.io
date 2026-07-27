<?php

declare(strict_types=1);

namespace Tests\Testo\Dungeon\Cards;

use App\Dungeon\Cards\ProtectionMinor;
use App\Dungeon\Events\CardUpdated;
use App\Dungeon\Events\PassiveCardUnset;
use App\Dungeon\Events\PlayerHealthDecreased;
use App\Dungeon\Events\PlayerHealthIncreased;
use App\Dungeon\Events\PlayerMoved;
use App\Dungeon\Point;
use Testo\Assert;
use Testo\Test;
use Tests\Testo\Dungeon\DungeonTest;

#[Test]
final class ProtectionMinorTest extends DungeonTest
{
    public function handle_absorbs_damage_when_shield_has_enough(): void
    {
        $card = new ProtectionMinor(); // toAbsorb = 50
        $this->dungeon->setPassiveCard($card);
        $this->dungeon->health = 80;

        $card->handle($this->dungeon, new PlayerHealthDecreased(20, 80, null));

        Assert::same($card->toAbsorb, 30);
        Assert::same($this->dungeon->health, 100);
        Assert::notNull($this->dungeon->passiveCard);
        $this->eventBus->assertDispatched(PlayerHealthIncreased::class, function (PlayerHealthIncreased $event) {
            Assert::same($event->amount, 20);
        });
        $this->eventBus->assertDispatched(CardUpdated::class);
    }

    public function handle_depletes_shield_and_unsets_passive_card_when_damage_exceeds_absorption(): void
    {
        $card = new ProtectionMinor();
        $card->toAbsorb = 10;
        $this->dungeon->setPassiveCard($card);
        $this->dungeon->health = 70;

        $card->handle($this->dungeon, new PlayerHealthDecreased(30, 70, null));

        // Only absorbs 10 (toAbsorb)
        Assert::same($this->dungeon->health, 80);
        Assert::null($this->dungeon->passiveCard);
        $this->eventBus->assertDispatched(PassiveCardUnset::class);
        $this->eventBus->assertDispatched(PlayerHealthIncreased::class, function (PlayerHealthIncreased $event) {
            Assert::same($event->amount, 10);
        });
    }

    public function handle_ignores_unrelated_events(): void
    {
        $card = new ProtectionMinor();

        $card->handle($this->dungeon, new PlayerMoved(
            from: new Point(0, 0),
            to: new Point(1, 0),
        ));

        Assert::same($card->toAbsorb, 50);
        $this->eventBus->assertNotDispatched(CardUpdated::class);
    }
}
