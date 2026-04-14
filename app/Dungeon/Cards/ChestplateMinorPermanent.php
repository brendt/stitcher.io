<?php

namespace App\Dungeon\Cards;

use App\Dungeon\Board;
use App\Dungeon\Cards\Support\CanBuyWithShards;
use App\Dungeon\Cards\Support\Card;
use App\Dungeon\Cards\Support\CardTrait;
use App\Dungeon\Cards\Support\HandlesEvents;
use App\Dungeon\Cards\Support\Rarity;
use App\Dungeon\Cards\Support\Type;
use App\Dungeon\Commands\ChangeHealth;
use App\Dungeon\Events\DamageDealt;
use App\Dungeon\Level;
use App\Dungeon\Tile;

final class ChestplateMinorPermanent implements Card, HandlesEvents, CanBuyWithShards
{
    use CardTrait;

    public function getName(): string
    {
        return "Chestplate";
    }

    public function getDescription(): string
    {
        return "-5 damage every hit";
    }

    public function getMana(): int
    {
        return 100;
    }

    public function getRarity(): Rarity
    {
        return Rarity::RARE;
    }

    public function getType(): Type
    {
        return Type::PERMANENT;
    }

    public function getImage(): string
    {
        return '/cards/chestplate-minor.png';
    }

    public function play(Board $board): void
    {
        $board->addPermanentCard($this);
    }

    public function handle(Board $board, Tile $tile, object $event): void
    {
        if (! $event instanceof DamageDealt) {
            return;
        }

        command(new ChangeHealth(min(5, $event->damage)));
    }

    public function getLevel(): Level
    {
        return Level::MASTER;
    }

    public function getPrice(): int
    {
        return 2000;
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
