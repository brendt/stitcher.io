<?php

namespace Tests\Dungeon;

use App\Dungeon\Cards\ProtectionMajor;
use App\Dungeon\Events\CardUpdated;
use App\Dungeon\Events\PassiveCardUnset;
use App\Dungeon\Events\PlayerHealthDecreased;
use App\Dungeon\Events\PlayerHealthIncreased;
use PHPUnit\Framework\Attributes\Test;

final class ProtectionMajorTest extends DungeonTest
{
    #[Test]
    public function handle_absorbs_damage_when_shield_has_enough(): void
    {
        $card = new ProtectionMajor(); // toAbsorb = 100
        $this->dungeon->setPassiveCard($card);
        $this->dungeon->health = 70;

        $card->handle($this->dungeon, new PlayerHealthDecreased(30, 70, null));

        $this->assertSame(70, $card->toAbsorb);
        $this->assertSame(100, $this->dungeon->health);
        $this->assertNotNull($this->dungeon->passiveCard);
        $this->eventBus->assertDispatched(PlayerHealthIncreased::class, function (PlayerHealthIncreased $event) {
            $this->assertSame(30, $event->amount);
        });
        $this->eventBus->assertDispatched(CardUpdated::class);
    }

    #[Test]
    public function handle_depletes_shield_and_unsets_passive_card_when_damage_exceeds_absorption(): void
    {
        $card = new ProtectionMajor();
        $card->toAbsorb = 20;
        $this->dungeon->setPassiveCard($card);
        $this->dungeon->health = 70;

        $card->handle($this->dungeon, new PlayerHealthDecreased(30, 70, null));

        // Absorbs 20 (toAbsorb), not the full 30
        $this->assertSame(90, $this->dungeon->health);
        $this->assertNull($this->dungeon->passiveCard);
        $this->eventBus->assertDispatched(PassiveCardUnset::class);
        $this->eventBus->assertDispatched(PlayerHealthIncreased::class, function (PlayerHealthIncreased $event) {
            $this->assertSame(20, $event->amount);
        });
    }

    #[Test]
    public function handle_ignores_unrelated_events(): void
    {
        $card = new ProtectionMajor();

        $card->handle($this->dungeon, new \App\Dungeon\Events\PlayerMoved(
            from: new \App\Dungeon\Point(0, 0),
            to: new \App\Dungeon\Point(1, 0),
        ));

        $this->assertSame(100, $card->toAbsorb);
        $this->eventBus->assertNotDispatched(CardUpdated::class);
    }
}
