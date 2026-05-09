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

final class ProtectionMajor implements Card, PassiveCard
{
    use IsCard;

    public int $toAbsorb = 100;

    private(set) string $name = 'Protection++';

    private(set) string $description = "Absorbs 100 damage";

    private(set) string $image = '/cards/protection-major.png';

    private(set) int $mana = 100;

    private(set) Rarity $rarity = Rarity::RARE;

    private(set) int $price = 5000;

    private(set) Type $type = Type::PASSIVE;

    private(set) Level $level = Level::MASTER;

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
            $overflow = $damage - $this->toAbsorb;
            $dungeon->increaseHealth($damage - $overflow);
            $dungeon->unsetPassiveCard();
        }

        $dungeon->updateCard($this);
    }
}
