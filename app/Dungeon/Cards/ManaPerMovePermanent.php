<?php

namespace App\Dungeon\Cards;

use App\Dungeon\Board;
use App\Dungeon\Cards\Support\CanBuyWithShards;
use App\Dungeon\Cards\Support\Card;
use App\Dungeon\Cards\Support\CardTrait;
use App\Dungeon\Cards\Support\HandlesEvents;
use App\Dungeon\Cards\Support\Rarity;
use App\Dungeon\Cards\Support\Type;
use App\Dungeon\Commands\ChangeMana;
use App\Dungeon\Events\PlayerMoved;
use App\Dungeon\Level;
use App\Dungeon\Tile;

final class ManaPerMovePermanent implements Card, HandlesEvents, CanBuyWithShards
{
    use CardTrait;

    public function getName(): string
    {
        return "Mana Stride";
    }

    public function getDescription(): string
    {
        return "+1 mana per move";
    }

    public function getMana(): int
    {
        return 50;
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
        return '/cards/mana-per-move-permanent.png';
    }

    public function play(Board $board): void
    {
        $board->addPermanentCard($this);
    }

    public function handle(Board $board, Tile $tile, object $event): void
    {
        if (! $event instanceof PlayerMoved) {
            return;
        }

        command(new ChangeMana(1));
    }

    public function getLevel(): Level
    {
        return Level::MASTER;
    }

    public function getPrice(): int
    {
        return 4000;
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
