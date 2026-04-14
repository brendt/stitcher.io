<?php

namespace App\Dungeon\Cards;

use App\Dungeon\Board;
use App\Dungeon\Cards\Support\CanBuyWithShards;
use App\Dungeon\Cards\Support\Card;
use App\Dungeon\Cards\Support\CardTrait;
use App\Dungeon\Cards\Support\HandlesEvents;
use App\Dungeon\Cards\Support\Rarity;
use App\Dungeon\Cards\Support\Type;
use App\Dungeon\Commands\ChangeStability;
use App\Dungeon\Events\PlayerMoved;
use App\Dungeon\Events\StabilityForGeneratedTileDecreased;
use App\Dungeon\Level;
use App\Dungeon\Tile;

final class StabilityPerTilePermanent implements Card, HandlesEvents, CanBuyWithShards
{
    use CardTrait;

    public function getName(): string
    {
        return "Stable Walk";
    }

    public function getDescription(): string
    {
        return "+1 stability per discovered tile";
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
        return '/cards/stability-permanent.png';
    }

    public function play(Board $board): void
    {
        $board->addPermanentCard($this);
    }

    public function handle(Board $board, Tile $tile, object $event): void
    {
        if (! $event instanceof StabilityForGeneratedTileDecreased) {
            return;
        }

        command(new ChangeStability(1));
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
