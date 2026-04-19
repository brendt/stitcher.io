<?php

namespace App\Dungeon\Cards;

use App\Dungeon\Dungeon;
use App\Dungeon\Card;
use App\Dungeon\PassiveCard;
use App\Dungeon\Rarity;
use App\Dungeon\Type;
use App\Dungeon\Events\PlayerMoved;
use App\Dungeon\Level;
use App\Dungeon\Tile;

final class BeaconMajor implements Card, PassiveCard
{
    use IsCard;

    public int $count = 25;

    private(set) string $name = 'Beacon++';

    public string $description {
        get => "Illuminate the darkness for {$this->count} moves";
    }

    public ?string $label {
        get => $this->count;
    }

    private(set) string $image = '/cards/beacon-major.png';

    private(set) int $mana = 75;

    private(set) Rarity $rarity = Rarity::EPIC;

    private(set) int $price = 10_000;

    private(set) Type $type = Type::PASSIVE;

    private(set) Level $level = Level::MASTER;

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
