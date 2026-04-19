<?php

namespace App\Dungeon\Cards;

use App\Dungeon\Dungeon;
use App\Dungeon\Card;
use App\Dungeon\DungeonEvent;
use App\Dungeon\Events\PlayerHealthDecreased;
use App\Dungeon\PassiveCard;
use App\Dungeon\Rarity;
use App\Dungeon\Type;
use App\Dungeon\Level;

final class ProtectionMinor implements Card, PassiveCard
{
    use IsCard;

    public int $toAbsorb = 50;

    private(set) string $name = 'Protection';

    private(set) string $description = "Absorbs 50 damage";

    private(set) string $image = '/cards/protection-minor.png';

    private(set) int $mana = 50;

    private(set) Rarity $rarity = Rarity::COMMON;

    private(set) int $price = 2000;

    private(set) Type $type = Type::PASSIVE;

    private(set) Level $level = Level::NOVICE;

    public ?string $label {
        get => $this->toAbsorb;
    }

    public function play(Dungeon $dungeon): void
    {
        // Nothing on play
    }

    public function handle(Dungeon $dungeon, DungeonEvent $event): void
    {
        if (! $event instanceof PlayerHealthDecreased) {
            return;
        }

        $damage = $event->amount;

        if ($this->toAbsorb > $damage) {
            $dungeon->increaseHealth($damage);
            $this->toAbsorb -= $damage;
        } else {
            $dungeon->increaseHealth($this->toAbsorb);
            $dungeon->unsetPassiveCard();
        }

        $dungeon->updateCard($this);
    }
}
