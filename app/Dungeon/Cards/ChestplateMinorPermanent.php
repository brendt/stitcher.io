<?php

namespace App\Dungeon\Cards;

use App\Dungeon\Board;
use App\Dungeon\Dungeon;
use App\Dungeon\Cards\Support\CanBuyWithShards;
use App\Dungeon\Card;
use App\Dungeon\Cards\Support\HandlesEvents;
use App\Dungeon\Rarity;
use App\Dungeon\Type;
use App\Dungeon\Commands\ChangeHealth;
use App\Dungeon\Events\DamageDealt;
use App\Dungeon\Level;
use App\Dungeon\Tile;

final class ChestplateMinorPermanent implements Card, HandlesEvents, CanBuyWithShards
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

    public function play(Dungeon $dungeon): void
    {
        // $board->addPermanentCard($this);
    }

    public function handle(Board $board, Tile $tile, object $event): void
    {
        if (! $event instanceof DamageDealt) {
            return;
        }

        command(new ChangeHealth(min(5, $event->damage)));
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
