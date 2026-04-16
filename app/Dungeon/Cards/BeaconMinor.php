<?php

namespace App\Dungeon\Cards;

use App\Dungeon\Dungeon;
use App\Dungeon\Card;
use App\Dungeon\WithEvents;
use App\Dungeon\Rarity;
use App\Dungeon\Type;
use App\Dungeon\Commands\ChangeHealth;
use App\Dungeon\Commands\DiscardPassiveCard;
use App\Dungeon\Commands\HideDweller;
use App\Dungeon\Commands\ShowDweller;
use App\Dungeon\Events\DamageDealt;
use App\Dungeon\Events\PlayerMoved;
use App\Dungeon\Level;
use App\Dungeon\Tile;

final class BeaconMinor implements Card, WithEvents
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

    public function play(Dungeon $dungeon): void
    {
        foreach ($dungeon->loopDwellers() as $dweller) {
            $dungeon->showDweller($dweller);
        }
    }

    public function handle(Dungeon $dungeon, Tile $tile, object $event): void
    {
        if (! $event instanceof PlayerMoved) {
            return;
        }

        $this->count -= 1;

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
