<?php

namespace App\Dungeon\Cards;

use App\Dungeon\Dungeon;
use App\Dungeon\Card;
use App\Dungeon\WithEvents;
use App\Dungeon\Rarity;
use App\Dungeon\Type;
use App\Dungeon\Commands\DiscardPassiveCard;
use App\Dungeon\Commands\HideDweller;
use App\Dungeon\Commands\ShowDweller;
use App\Dungeon\Events\PlayerMoved;
use App\Dungeon\Level;
use App\Dungeon\Tile;

final class BeaconMajor implements Card, WithEvents
{
    use IsCard;

    public int $count = 25;

    private(set) string $name = 'Beacon++';

    private(set) string $description = "Illuminate the darkness for 25 moves";

    private(set) string $image = '/cards/beacon-major.png';

    private(set) int $mana = 75;

    private(set) Rarity $rarity = Rarity::EPIC;

    private(set) int $price = 10_000;

    private(set) Type $type = Type::PASSIVE;

    private(set) Level $level = Level::MASTER;

    public function play(Dungeon $dungeon): void
    {
        // foreach ($board->getAllDwellers() as $dweller) {
        // command(new ShowDweller($dweller->point));
        // }
    }

    public function handle(Dungeon $dungeon, Tile $tile, object $event): void
    {
        if (! $event instanceof PlayerMoved) {
            return;
        }

        $this->count -= 1;

        if ($this->count === 0) {
            foreach ($board->getAllDwellers() as $dweller) {
                command(new HideDweller($dweller->point));
            }
            
            $dungeon->unsetPassiveCard();
        }
    }
}
