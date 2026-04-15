<?php

namespace App\Dungeon;

use App\Dungeon\Events\ActiveCardSet;
use App\Dungeon\Events\ActiveCardUnset;
use App\Dungeon\Events\CardDrawn;
use App\Dungeon\Events\CardPlayed;
use App\Dungeon\Events\PassiveCardSet;
use App\Dungeon\Events\PassiveCardUnset;
use App\Dungeon\Events\PermanentCardAdded;
use App\Dungeon\Events\PlayerManaGained;
use App\Dungeon\Events\PlayerManaLost;
use App\Dungeon\Events\PlayerMoved;
use App\Dungeon\Events\PlayerStabilityDecreased;
use App\Dungeon\Events\TileCoinsAdded;
use App\Dungeon\Events\TileCoinsCollected;
use App\Dungeon\Events\TileCollapsed;
use App\Dungeon\Events\TileUpdated;
use function Tempest\EventBus\event;
use function Tempest\Support\arr;

trait DungeonActions
{
    public function move(Direction $direction): void
    {
        if (! $this->currentTile->canMoveTo($direction)) {
            return;
        }

        $neighbourPosition = $this->playerPosition->getNeighbour($direction);

        $neighbourTile = $this->tryTile($neighbourPosition);

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

        $event = new PlayerMoved($oldPosition, $this->playerPosition);

        event($event);

        $this->notifyCards($this->getTile($event->to), $event);
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

    public function loseMana(int $amount): void
    {
        if ($amount <= 0) {
            return;
        }

        $this->mana -= $amount;

        if ($this->mana <= 0) {
            $this->mana = 0;
        }

        event(new PlayerManaLost($amount));
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

    public function playCard(string|Card $card): void
    {
        if (is_string($card)) {
            $card = $this->hand[$card] ?? null;
        } else {
            $card = $this->hand[$card->id] ?? null;
        }

        if (! $card) {
            return;
        }

        if ($card->mana > $this->mana) {
            return;
        }

        if ($card->type->isActive() && $this->activeCard) {
            return;
        }

        if ($card->type->isPassive() && $this->passiveCard) {
            return;
        }

        $this->loseMana($card->mana);

        unset($this->hand[$card->id]);

        $card->play($this);

        event(new CardPlayed($card));
    }

    public function drawCard(): void
    {
        if (count($this->hand) >= $this->maxHandCount) {
            return;
        }

        if ($this->deck === []) {
            return;
        }

        $card = arr($this->deck)->random();

        $this->hand[$card->id] = $card;
        unset($this->deck[$card->id]);

        event(new CardDrawn($card));
    }

    public function setPassiveCard(Card $card): void
    {
        $this->passiveCard = $card;

        event(new PassiveCardSet($card));
    }

    public function unsetPassiveCard(): void
    {
        $this->passiveCard = null;

        event(new PassiveCardUnset());
    }

    public function setActiveCard(Card $card): void
    {
        $this->activeCard = $card;

        event(new ActiveCardSet($card));
    }

    public function unsetActiveCard(): void
    {
        $this->activeCard = null;

        event(new ActiveCardUnset());
    }

    public function addPermanentCard(Card $card): void
    {
        $this->permanentCards[$card->id] = $card;

        event(new PermanentCardAdded($card));
    }

    public function notifyCards(Tile $targetTile, object $event): void
    {
        $activeCard = $this->activeCard;

        if ($activeCard instanceof WithEvents) {
            $activeCard->handle($this, $targetTile, $event);
        }

        $passiveCard = $this->passiveCard;

        if ($passiveCard instanceof WithEvents) {
            $passiveCard->handle($this, $targetTile, $event);
        }

        foreach ($this->permanentCards as $permanentCard) {
            if ($permanentCard instanceof WithEvents) {
                $permanentCard->handle($this, $targetTile, $event);
            }
        }
    }

    public function interactWithTile(Point $point): void
    {
        $card = $this->activeCard;

        if (! $card instanceof InteractsWithTile) {
            return;
        }

        $tile = $this->tryTile($point);

        if (! $tile) {
            return;
        }

        if (! $card->canInteractWithTile($this, $tile)) {
            return;
        }

        $card->interactWithTile($this, $tile);
    }

    public function removeTileWalls(Tile $tile): void
    {
        $tile->directions = Direction::cases();

        event(new TileUpdated($tile));

        foreach ($tile->directions as $direction) {
            $neighbourPosition = $tile->point->getNeighbour($direction);

            $neighbourTile = $this->tryTile($neighbourPosition);

            if (! $neighbourTile) {
                continue;
            }

            $neighbourTile->directions[] = $direction->opposite();

            event(new TileUpdated($neighbourTile));
        }
    }
}