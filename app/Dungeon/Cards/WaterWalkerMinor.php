<?php

namespace App\Dungeon\Cards;

use App\Dungeon\Card;
use App\Dungeon\Dungeon;
use App\Dungeon\DungeonEvent;
use App\Dungeon\Events\PlayerMoved;
use App\Dungeon\Level;
use App\Dungeon\PassiveCard;
use App\Dungeon\Rarity;
use App\Dungeon\Type;

final class WaterWalkerMinor implements Card, PassiveCard
{
    use IsCard;

    public string $name = 'Water Walker';

    public string $description = 'Walk on water, get to land in time, or drown.';

    public int $mana = 100;

    public Rarity $rarity = Rarity::RARE;

    public Type $type = Type::PASSIVE;

    public string $image = '/cards/water-walker-minor.png';

    public int $price = 15_000;

    public Level $level = Level::MASTER;

    public null|string $label {
        get => $this->moves;
    }

    public int $moves = 5;

    public function play(Dungeon $dungeon): void
    {
        $dungeon->canWalkOnWater = true;
    }

    public function handle(Dungeon $dungeon, DungeonEvent $event): void
    {
        if (! $event instanceof PlayerMoved) {
            return;
        }

        if ($this->moves > 0) {
            $this->moves -= 1;
        }

        $dungeon->updateCard($this);

        $tile = $dungeon->tryTile($event->to);

        if (! $tile) {
            return;
        }

        if ($this->moves <= 0) {
            if ($tile->isLake) {
                $dungeon->decreaseHealth(15, "You're drowning!");
            } else {
                $dungeon->canWalkOnWater = false;
                $dungeon->unsetPassiveCard();
            }
        }
    }
}