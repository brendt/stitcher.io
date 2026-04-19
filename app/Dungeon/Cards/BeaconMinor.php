<?php

namespace App\Dungeon\Cards;

use App\Dungeon\Dungeon;
use App\Dungeon\Card;
use App\Dungeon\DungeonEvent;
use App\Dungeon\PassiveCard;
use App\Dungeon\Rarity;
use App\Dungeon\Type;
use App\Dungeon\Events\PlayerMoved;
use App\Dungeon\Level;

final class BeaconMinor implements Card, PassiveCard
{
    use IsCard;

    public int $count = 10;

    private(set) string $name = 'Beacon';

    private(set) string $description = "Illuminate the darkness for 10 moves";

    private(set) string $image = '/cards/beacon-minor.png';

    private(set) int $mana = 75;

    private(set) Rarity $rarity = Rarity::RARE;

    private(set) int $price = 2000;

    private(set) Type $type = Type::PASSIVE;

    private(set) Level $level = Level::NOVICE;

    public ?string $label {
        get => $this->count;
    }

    public function play(Dungeon $dungeon): void
    {
        foreach ($dungeon->loopDwellers() as $dweller) {
            $dungeon->showDweller($dweller);
        }
    }

    public function handle(Dungeon $dungeon, DungeonEvent $event): void
    {
        if (! $event instanceof PlayerMoved) {
            return;
        }

        $this->count -= 1;

        $dungeon->updateCard($this);

        foreach ($dungeon->loopDwellers() as $dweller) {
            if ($this->count > 0) {
                $dungeon->showDweller($dweller);
            } else {
                if (! $dungeon->withinVisibilityRadius($dweller->point)) {
                    $dungeon->hideDweller($dweller);
                }

                $dungeon->unsetPassiveCard();
            }
        }
    }
}
