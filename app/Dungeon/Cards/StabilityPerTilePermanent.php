<?php

namespace App\Dungeon\Cards;

use App\Dungeon\Dungeon;
use App\Dungeon\CanBuyWithShards;
use App\Dungeon\Card;
use App\Dungeon\WithEvents;
use App\Dungeon\Rarity;
use App\Dungeon\Type;
use App\Dungeon\Commands\ChangeStability;
use App\Dungeon\Events\PlayerMoved;
use App\Dungeon\Events\StabilityForGeneratedTileDecreased;
use App\Dungeon\Level;
use App\Dungeon\Tile;

final class StabilityPerTilePermanent implements Card, WithEvents, CanBuyWithShards
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

    public function play(Dungeon $dungeon): void
    {
        // $board->addPermanentCard($this);
    }

    public function handle(Dungeon $dungeon, Tile $tile, object $event): void
    {
        if (! $event instanceof StabilityForGeneratedTileDecreased) {
            return;
        }

        command(new ChangeStability(1));
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
