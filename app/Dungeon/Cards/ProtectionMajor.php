<?php

namespace App\Dungeon\Cards;

use App\Dungeon\Dungeon;
use App\Dungeon\Card;
use App\Dungeon\Events\PlayerHealthDecreased;
use App\Dungeon\WithEvents;
use App\Dungeon\Rarity;
use App\Dungeon\Type;
use App\Dungeon\Level;
use App\Dungeon\Tile;

final class ProtectionMajor implements Card, WithEvents
{
    use IsCard;

    public int $toAbsorb = 100;

    private(set) string $name = 'Protection++';

    private(set) string $description = "Absorbs 100 damage";

    private(set) string $image = '/cards/protection-major.png';

    private(set) int $mana = 100;

    private(set) Rarity $rarity = Rarity::RARE;

    private(set) int $price = 2500;

    private(set) Type $type = Type::PASSIVE;

    private(set) Level $level = Level::MASTER;

    public function play(Dungeon $dungeon): void
    {
        // Nothing on play
    }

    public function handle(Dungeon $dungeon, Tile $tile, object $event): void
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
    }
}
