<?php

namespace App\Dungeon\Cards;

use App\Dungeon\Dungeon;
use App\Dungeon\CanBuyWithShards;
use App\Dungeon\Card;
use App\Dungeon\WithEvents;
use App\Dungeon\Rarity;
use App\Dungeon\Type;
use App\Dungeon\Commands\ChangeHealth;
use App\Dungeon\Events\DamageDealt;
use App\Dungeon\Level;
use App\Dungeon\Tile;

final class ChestplateMajorPermanent implements Card, WithEvents, CanBuyWithShards
{
    use IsCard;

    private(set) string $name = "Chestplate++";

    private(set) string $description = "-10 damage every hit";

    private(set) int $mana = 150;

    private(set) Rarity $rarity = Rarity::RARE;

    private(set) Type $type = Type::PERMANENT;

    private(set) string $image = '/cards/chestplate-major.png';

    private(set) Level $level = Level::MASTER;

    private(set) int $price = 4000;

    public function getAdjustedPrice(): int
    {
        return 4000;
    }

    public function getShardPrice(): int
    {
        return 15;
    }

    public function play(Dungeon $dungeon): void
    {
        // $board->addPermanentCard($this);
    }

    public function handle(Dungeon $dungeon, Tile $tile, object $event): void
    {
        if (! $event instanceof DamageDealt) {
            return;
        }

        command(new ChangeHealth(min(10, $event->damage)));
    }
}
