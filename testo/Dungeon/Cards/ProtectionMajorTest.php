<?php

declare(strict_types=1);

namespace Tests\Testo\Dungeon\Cards;

use App\Dungeon\Cards\ProtectionMajor;
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
final class ProtectionMajorTest extends DungeonTest
{
    public function handle_absorbs_damage_when_shield_has_enough(): void
    {
        $card = new ProtectionMajor(); // toAbsorb = 100
        $this->dungeon->setPassiveCard($card);
        $this->dungeon->health = 70;

        $card->handle($this->dungeon, new PlayerHealthDecreased(30, 70, null));

        Assert::same($card->toAbsorb, 70);
        Assert::same($this->dungeon->health, 100);
        Assert::notNull($this->dungeon->passiveCard);
        $this->eventBus->assertDispatched(PlayerHealthIncreased::class, function (PlayerHealthIncreased $event) {
            Assert::same($event->amount, 30);
        });
        $this->eventBus->assertDispatched(CardUpdated::class);
    }

    public function handle_depletes_shield_and_unsets_passive_card_when_damage_exceeds_absorption(): void
    {
        $card = new ProtectionMajor();
        $card->toAbsorb = 20;
        $this->dungeon->setPassiveCard($card);
        $this->dungeon->health = 70;

        $card->handle($this->dungeon, new PlayerHealthDecreased(30, 70, null));

        // Absorbs 20 (toAbsorb), not the full 30
        Assert::same($this->dungeon->health, 90);
        Assert::null($this->dungeon->passiveCard);
        $this->eventBus->assertDispatched(PassiveCardUnset::class);
        $this->eventBus->assertDispatched(PlayerHealthIncreased::class, function (PlayerHealthIncreased $event) {
            Assert::same($event->amount, 20);
        });
    }

    public function handle_ignores_unrelated_events(): void
    {
        $card = new ProtectionMajor();

        $card->handle($this->dungeon, new PlayerMoved(
            from: new Point(0, 0),
            to: new Point(1, 0),
        ));

        Assert::same($card->toAbsorb, 100);
        $this->eventBus->assertNotDispatched(CardUpdated::class);
    }
}
