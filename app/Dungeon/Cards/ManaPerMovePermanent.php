<?php

namespace App\Dungeon\Cards;

use App\Dungeon\Dungeon;
use App\Dungeon\Cards\Support\CanBuyWithShards;
use App\Dungeon\Card;
use App\Dungeon\WithEvents;
use App\Dungeon\Rarity;
use App\Dungeon\Type;
use App\Dungeon\Commands\ChangeMana;
use App\Dungeon\Events\PlayerMoved;
use App\Dungeon\Level;
use App\Dungeon\Tile;

final class ManaPerMovePermanent implements Card, WithEvents, CanBuyWithShards
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

    public function play(Dungeon $dungeon): void
    {
        // $board->addPermanentCard($this);
    }

    public function handle(Dungeon $dungeon, Tile $tile, object $event): void
    {
        if (! $event instanceof PlayerMoved) {
            return;
        }

        command(new ChangeMana(1));
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
