<?php

namespace App\Dungeon\Cards;

use App\Dungeon\Dungeon;
use App\Dungeon\CanBuyWithShards;
use App\Dungeon\Card;
use App\Dungeon\DungeonEvent;
use App\Dungeon\Events\TileGenerated;
use App\Dungeon\PassiveCard;
use App\Dungeon\Rarity;
use App\Dungeon\Type;
use App\Dungeon\Level;

final class StabilityPerTilePermanent implements Card, PassiveCard, CanBuyWithShards
{
    use IsCard;

    private(set) string $name = "Stable Walk";

    private(set) string $description = "+1 stability per discovered tile";

    private(set) int $mana = 100;

    private(set) Rarity $rarity = Rarity::RARE;

    private(set) Type $type = Type::PERMANENT;

    private(set) string $image = '/cards/stability-permanent.png';

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
        if (! $event instanceof TileGenerated) {
            return;
        }

        $dungeon->updateCard($this);
        $dungeon->increaseStability(1);
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
