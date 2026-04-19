<?php

namespace App\Dungeon\Cards;

use App\Dungeon\Dungeon;
use App\Dungeon\CanBuyWithShards;
use App\Dungeon\Card;
use App\Dungeon\DungeonEvent;
use App\Dungeon\PassiveCard;
use App\Dungeon\Rarity;
use App\Dungeon\Type;
use App\Dungeon\Events\PlayerMoved;
use App\Dungeon\Level;

final class ManaPerMovePermanent implements Card, PassiveCard, CanBuyWithShards
{
    use IsCard;

    private(set) string $name = "Mana Stride";

    private(set) string $description = "+1 mana per move";

    private(set) int $mana = 50;

    private(set) Rarity $rarity = Rarity::RARE;

    private(set) Type $type = Type::PERMANENT;

    private(set) string $image = '/cards/mana-per-move-permanent.png';

    private(set) Level $level = Level::MASTER;

    private(set) int $price = 4000;

    public ?string $label {
        get => null;
    }

    public function play(Dungeon $dungeon): void
    {
        // Nothing on play
    }

    public function handle(Dungeon $dungeon, DungeonEvent $event): void
    {
        if (! $event instanceof PlayerMoved) {
            return;
        }

        $dungeon->updateCard($this);

        $dungeon->increaseMana(1);
    }

    public function getAdjustedPrice(): int
    {
        return 4000;
    }

    public function getShardPrice(): int
    {
        return 10;
    }
}
