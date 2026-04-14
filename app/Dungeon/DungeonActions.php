<?php

namespace App\Dungeon;

use App\Dungeon\Entities\Tile;
use App\Dungeon\Events\PlayerManaGained;
use App\Dungeon\Events\PlayerStabilityDecreased;
use App\Dungeon\Events\TileCoinsAdded;
use App\Dungeon\Events\PlayerMoved;
use App\Dungeon\Events\TileCoinsCollected;
use App\Dungeon\Events\TileCollapsed;
use function Tempest\EventBus\event;

trait DungeonActions
{
    public function move(Direction $direction): void
    {
        if (! $this->currentTile->canMoveTo($direction)) {
            return;
        }

        $neighbourPosition = $this->playerPosition->getNeighbour($direction);

        $neighbourTile = $this->getTile($neighbourPosition);

        if ($neighbourTile && $neighbourTile->isCollapsed) {
            return;
        }

        if ($neighbourTile && ! $neighbourTile->canMoveTo($direction->opposite())) {
            return;
        }

        $oldPosition = $this->playerPosition;
        $this->playerPosition = $neighbourPosition;

        if (! $neighbourTile) {
            $this->generateTile($oldPosition, $this->playerPosition);
        }

        event(new PlayerMoved($oldPosition, $this->playerPosition));
    }

    public function addCoinsToTile(Tile $tile, int $amount): void
    {
        if ($tile->coins >= 10) {
            return;
        }

        $tile->coins += $amount;

        event(new TileCoinsAdded($tile, $amount));
    }

    public function collectCoins(Tile $tile): void
    {
        $collected = $tile->coins;

        $this->coins += $collected;

        $tile->coins = 0;

        event(new TileCoinsCollected($tile, $collected));
    }

    public function gainMana(int $amount): void
    {
        if ($this->mana >= $this->maxMana) {
            return;
        }

        if ($amount <= 0) {
            return;
        }

        $this->mana += $amount;

        if ($this->mana >= $this->maxMana) {
            $this->mana = $this->maxMana;
        }

        event(new PlayerManaGained($amount));
    }

    public function decreaseStability(int $amount): void
    {
        if ($this->stability <= 0) {
            return;
        }

        $this->stability -= $amount;

        if ($this->stability <= 0) {
            $this->stability = 0;
        }

        if ($amount <= 0) {
            return;
        }

        event(new PlayerStabilityDecreased($amount));
    }

    public function collapseTile(Tile $tile): void
    {
        if (! $tile->canCollapse()) {
            return;
        }

        $tile->isCollapsed = true;

        event(new TileCollapsed($tile));
    }
}