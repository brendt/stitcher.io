<?php

namespace App\Dungeon\Cards;

use App\Dungeon\Dungeon;
use App\Dungeon\CanBuyWithShards;
use App\Dungeon\Card;
use App\Dungeon\DungeonEvent;
use App\Dungeon\Events\PlayerHealthDecreased;
use App\Dungeon\PassiveCard;
use App\Dungeon\Rarity;
use App\Dungeon\Type;
use App\Dungeon\Level;

final class ChestplateMinorPermanent implements Card, PassiveCard, CanBuyWithShards
{
    use IsCard;

    private(set) string $name = "Chestplate";

    private(set) string $description = "-5 damage every hit";

    private(set) int $mana = 100;

    private(set) Rarity $rarity = Rarity::RARE;

    private(set) Type $type = Type::PERMANENT;

    private(set) string $image = '/cards/chestplate-minor.png';

    private(set) Level $level = Level::MASTER;

    private(set) int $price = 2000;

    public ?string $label {
        get => null;
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

        $dungeon->updateCard($this);
        $dungeon->increaseHealth(min(5, $event->amount));
    }

    public function getAdjustedPrice(): int
    {
        return 2000;
    }

    public function getShardPrice(): int
    {
        return 10;
    }
}
