<?php

namespace App\Dungeon;

use App\Dungeon\Events\ActiveCardSet;
use App\Dungeon\Events\ActiveCardUnset;
use App\Dungeon\Events\ArtifactCollected;
use App\Dungeon\Events\ArtifactSpawned;
use App\Dungeon\Events\CardDrawn;
use App\Dungeon\Events\CardPlayed;
use App\Dungeon\Events\PlayerExited;
use App\Dungeon\Events\DwellerDespawned;
use App\Dungeon\Events\DwellerMoved;
use App\Dungeon\Events\DwellerSpawned;
use App\Dungeon\Events\DwellerUpdated;
use App\Dungeon\Events\PassiveCardSet;
use App\Dungeon\Events\PassiveCardUnset;
use App\Dungeon\Events\PermanentCardAdded;
use App\Dungeon\Events\PlayerCoinsIncreased;
use App\Dungeon\Events\PlayerHealthDecreased;
use App\Dungeon\Events\PlayerHealthIncreased;
use App\Dungeon\Events\PlayerManaIncreased;
use App\Dungeon\Events\PlayerManaDecreased;
use App\Dungeon\Events\PlayerMaxHealthIncreased;
use App\Dungeon\Events\PlayerMaxManaIncreased;
use App\Dungeon\Events\PlayerMoved;
use App\Dungeon\Events\PlayerStabilityDecreased;
use App\Dungeon\Events\PlayerStabilityIncreased;
use App\Dungeon\Events\TileCoinsAdded;
use App\Dungeon\Events\TileCoinsCollected;
use App\Dungeon\Events\TileCollapsed;
use App\Dungeon\Events\TileGenerated;
use App\Dungeon\Events\TileUpdated;
use App\Dungeon\Events\VisibilityChanged;
use function Tempest\EventBus\event;
use function Tempest\Support\arr;

trait DungeonActions
{
    public function generateTile(?Point $from, Point $to): void
    {
        if ($this->tryTile($to)) {
            return;
        }

        $absX = abs($to->x);
        $absY = abs($to->y);

        $minimumDirectionCount = match (true) {
            $absX < 1 && $absY < 1 => 3,
            $absX < 6 && $absY < 6 => 2,
            default => 1,
        };

        $allowedDirections = array_rand(Direction::cases(), rand($minimumDirectionCount, 4));

        if (! is_array($allowedDirections)) {
            $allowedDirections = [$allowedDirections];
        }

        $directions = arr(Direction::cases())
            ->filter(fn (Direction $direction, int $index) => in_array($index, $allowedDirections, strict: true));

        if ($from) {
            $directions = $directions->add($to->directionTo($from));
        }

        $directions = $directions->unique()
            ->values()
            ->toArray();

        $tile = new Tile($to, directions: $directions);

        if (random_int(1, 100) === 1) {
            $tile->isTrapped = true;
        }

        $this->addTile($tile);

        event(new TileGenerated($tile));
    }

    public function move(Direction $direction): void
    {
        if (! $this->cheat && ! $this->currentTile->canMoveTo($direction)) {
            return;
        }

        $neighbourPosition = $this->playerPosition->getNeighbour($direction);

        $neighbourTile = $this->tryTile($neighbourPosition);

        if (! $this->cheat && $neighbourTile && $neighbourTile->isCollapsed) {
            return;
        }

        if (! $this->cheat && $neighbourTile && ! $neighbourTile->canMoveTo($direction->opposite())) {
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

    public function increaseMaxMana(int $amount): void
    {
        $this->maxMana += $amount;

        event(new PlayerMaxManaIncreased($amount));
    }

    public function increaseMana(int $amount): void
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

        event(new PlayerManaIncreased($amount));
    }

    public function decreaseMana(int $amount): void
    {
        if ($amount <= 0) {
            return;
        }

        $this->mana -= $amount;

        if ($this->mana <= 0) {
            $this->mana = 0;
        }

        event(new PlayerManaDecreased($amount));
    }

    public function increaseCoins(int $amount): void
    {
        $this->coins += $amount;

        event(new PlayerCoinsIncreased($amount));
    }

    public function increaseMaxHealth(int $amount): void
    {
        $this->maxHealth += $amount;

        event(new PlayerMaxHealthIncreased($amount));
    }

    public function increaseHealth(int $amount): void
    {
        if ($this->health >= $this->maxHealth) {
            return;
        }

        if ($amount <= 0) {
            return;
        }

        $this->health += $amount;

        if ($this->health >= $this->maxHealth) {
            $this->health = $this->maxHealth;
        }

        event(new PlayerHealthIncreased($amount));
    }

    public function decreaseHealth(int $amount): void
    {
        if ($amount <= 0) {
            return;
        }

        $this->health -= $amount;

        if ($this->health <= 0) {
            $this->health = 0;
        }

        event(new PlayerHealthDecreased($amount));
    }

    public function decreaseStability(int $amount): void
    {
        if ($this->stability <= 0) {
            return;
        }

        if ($amount <= 0) {
            return;
        }

        $this->stability -= $amount;

        if ($this->stability < 0) {
            $this->stability = 0;
        }

        event(new PlayerStabilityDecreased($amount));
    }

    public function increaseStability(int $amount): void
    {
        if ($this->stability >= $this->maxStability) {
            return;
        }

        if ($amount <= 0) {
            return;
        }

        $this->stability += $amount;

        if ($this->stability >= $this->maxStability) {
            $this->stability = $this->maxStability;
        }

        event(new PlayerStabilityIncreased($amount));
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

        if ($card instanceof CheckBeforePlaying && ! $card->canPlay($this)) {
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

        $this->decreaseMana($card->mana);

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

        $id = array_rand($this->deck);

        $card = $this->deck[$id];
        $this->hand[$id] = $card;
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
        if (! $this->withinVisibilityRadius($point)) {
            return;
        }

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

        // TODO: update card
    }

    public function removeTileCollapse(Tile $tile): void
    {
        if (! $tile->isCollapsed) {
            return;
        }

        $tile->isCollapsed = false;

        event(new TileUpdated($tile));
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

    public function spawnDweller(?Point $point = null): void
    {
        $point ??= new Point(
            x: random_int(-25, 25),
            y: random_int(-25, 25),
        );

        $dweller = new Dweller($point);
        $this->dwellers[$point->x][$point->y] = $dweller;
        $dweller->isVisible = $this->hasTile($dweller->point) && $this->withinVisibilityRadius($dweller->point);

        event(new DwellerSpawned($dweller));
    }

    public function despawnDweller(Dweller $dweller): void
    {
        unset($this->dwellers[$dweller->point->x][$dweller->point->y]);

        event(new DwellerDespawned(
            dweller: $dweller,
        ));
    }

    public function moveDweller(Dweller $dweller, Point $to): void
    {
        $from = $dweller->point;

        $dweller->point = $to;
        unset($this->dwellers[$from->x][$from->y]);
        $this->dwellers[$to->x][$to->y] = $dweller;
        $dweller->isVisible = $this->hasTile($dweller->point) && $this->withinVisibilityRadius($dweller->point);

        event(new DwellerMoved(
            dweller: $dweller,
            from: $from,
            to: $to,
        ));
    }

    public function showDweller(Dweller $dweller): void
    {
        if ($dweller->isVisible) {
            return;
        }

        $dweller->isVisible = true;

        event(new DwellerUpdated($dweller));
    }

    public function hideDweller(Dweller $dweller): void
    {
        if (! $dweller->isVisible) {
            return;
        }

        $dweller->isVisible = false;

        event(new DwellerUpdated($dweller));
    }

    public function changeVisibility(int $visibilityRadius): void
    {
        $this->visibilityRadius = $visibilityRadius;

        event(new VisibilityChanged($visibilityRadius));
    }

    public function spawnArtifact(?Point $point = null): void
    {
//        $max = match ($this->user->getLevel()) {
//            Level::NOOB => 20,
//            Level::NOVICE => 30,
//            default => 50,
//        };

        $max = 20;

        $this->artifactLocation = $point ?? new Point(rand(-1 * $max, $max), rand(-1 * $max, $max));

        if ($tile = $this->tryTile($this->artifactLocation)) {
            $tile->isTrapped = false;
            $tile->isCollapsed = false;
            $tile->directions = Direction::cases();

            event(new TileUpdated($tile));
        }

        event(new ArtifactSpawned($this->artifactLocation));
    }

    public function collectArtifact(): void
    {
        if (! $this->playerPosition->equals($this->artifactLocation)) {
            return;
        }

        $this->increaseCoins(random_int(100, 500));
        $this->decreaseStability(random_int(10, 25));
        $this->increaseMana(random_int(5, 20));

        event(new ArtifactCollected($this->currentTile));

        $this->spawnArtifact();
    }

    public function exit(bool $requiresOrigin = true): void
    {
        if ($requiresOrigin && ! $this->currentTile->isOrigin) {
            return;
        }

        $this->hasEnded = true;

        event(new PlayerExited());
    }
}