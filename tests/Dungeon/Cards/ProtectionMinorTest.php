<?php

namespace Tests\Dungeon\Cards;

use Tests\Dungeon\DungeonTest;

use App\Dungeon\Cards\ProtectionMinor;
use App\Dungeon\Events\CardUpdated;
use App\Dungeon\Events\PassiveCardUnset;
use App\Dungeon\Events\PlayerHealthDecreased;
use App\Dungeon\Events\PlayerHealthIncreased;
use PHPUnit\Framework\Attributes\Test;

final class ProtectionMinorTest extends DungeonTest
{
    #[Test]
    public function handle_absorbs_damage_when_shield_has_enough(): void
    {
        $card = new ProtectionMinor(); // toAbsorb = 50
        $this->dungeon->setPassiveCard($card);
        $this->dungeon->health = 80;

        $card->handle($this->dungeon, new PlayerHealthDecreased(20, 80, null));

        $this->assertSame(30, $card->toAbsorb);
        $this->assertSame(100, $this->dungeon->health);
        $this->assertNotNull($this->dungeon->passiveCard);
        $this->eventBus->assertDispatched(PlayerHealthIncreased::class, function (PlayerHealthIncreased $event) {
            $this->assertSame(20, $event->amount);
        });
        $this->eventBus->assertDispatched(CardUpdated::class);
    }

    #[Test]
    public function handle_depletes_shield_and_unsets_passive_card_when_damage_exceeds_absorption(): void
    {
        $card = new ProtectionMinor();
        $card->toAbsorb = 10;
        $this->dungeon->setPassiveCard($card);
        $this->dungeon->health = 70;

        $card->handle($this->dungeon, new PlayerHealthDecreased(30, 70, null));

        // Only absorbs 10 (toAbsorb)
        $this->assertSame(80, $this->dungeon->health);
        $this->assertNull($this->dungeon->passiveCard);
        $this->eventBus->assertDispatched(PassiveCardUnset::class);
        $this->eventBus->assertDispatched(PlayerHealthIncreased::class, function (PlayerHealthIncreased $event) {
            $this->assertSame(10, $event->amount);
        });
    }

    #[Test]
    public function handle_ignores_unrelated_events(): void
    {
        $card = new ProtectionMinor();

        $card->handle($this->dungeon, new \App\Dungeon\Events\PlayerMoved(
            from: new \App\Dungeon\Point(0, 0),
            to: new \App\Dungeon\Point(1, 0),
        ));

        $this->assertSame(50, $card->toAbsorb);
        $this->eventBus->assertNotDispatched(CardUpdated::class);
    }
}
