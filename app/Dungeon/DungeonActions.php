<?php

namespace App\Dungeon;

use App\Dungeon\Events\ActiveCardSet;
use App\Dungeon\Events\ActiveCardUnset;
use App\Dungeon\Events\ArtifactCollected;
use App\Dungeon\Events\ArtifactSpawned;
use App\Dungeon\Events\CardDrawn;
use App\Dungeon\Events\CardPlayed;
use App\Dungeon\Events\CardUpdated;
use App\Dungeon\Events\LakeDiscovered;
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
use App\Dungeon\Events\PlayerResigned;
use App\Dungeon\Events\PlayerShardsIncreased;
use App\Dungeon\Events\PlayerStabilityDecreased;
use App\Dungeon\Events\PlayerStabilityIncreased;
use App\Dungeon\Events\PlayerVictoryPointsIncreased;
use App\Dungeon\Events\RelicCollected;
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

        $discoveredLakeForEdge = $this->getLakeForEdge($to);

        if ($lake = $this->getLake($to)) {
            $depth = $lake->getLakePoint($to)?->depth;
            $tile->isLake = true;
            $tile->depth = $depth;
            $tile->directions = Direction::cases();
            if ($to->equals($lake->relic)) {
                $tile->isRelic = true;
            }
        } elseif ($discoveredLakeForEdge) {
            $tile->directions = Direction::cases();
        }  elseif (isset($this->victoryPointLocations[$to->x][$to->y])) {
            $tile->isVictoryPoint = true;
        } elseif (isset($this->shardLocations[$to->x][$to->y])) {
            $tile->isShard = true;
        } elseif (isset($this->healthAltars[$to->x][$to->y])) {
            $tile->isHealthAltar = true;
            $tile->directions = Direction::cases();
        } elseif (isset($this->manaAltars[$to->x][$to->y])) {
            $tile->isManaAltar = true;
            $tile->directions = Direction::cases();
        } elseif (isset($this->stabilityAltars[$to->x][$to->y])) {
            $tile->isStabilityAltar = true;
            $tile->directions = Direction::cases();
        } elseif ($this->level->shouldSpawnTrap() && $tile->canBeTrapped()) {
            $tile->isTrapped = true;
        }

        $this->addTile($tile);

        if ($discoveredLakeForEdge && ! $discoveredLakeForEdge->isDiscovered) {
            $discoveredLakeForEdge->isDiscovered = true;
            event(new LakeDiscovered($discoveredLakeForEdge, $tile));
        }

        $event = new TileGenerated($tile);

        event($event);
    }

    public function move(Direction $direction): void
    {
        if (! $this->cheat && ! $this->currentTile->canMoveTo($direction)) {
            return;
        }

        $neighbourPosition = $this->playerPosition->getNeighbour($direction);

        $neighbourTile = $this->tryTile($neighbourPosition);

        // Collapsed tiles
        if (! $this->cheat && $neighbourTile && $neighbourTile->isCollapsed) {
            return;
        }

        // Walls
        if (! $this->cheat && $neighbourTile && ! $neighbourTile->canMoveTo($direction->opposite())) {
            return;
        }

        // Lakes
        if (! $this->cheat && $neighbourTile && $neighbourTile->isLake && ! $this->canWalkOnWater) {
            return;
        }

        $oldPosition = $this->playerPosition;
        $this->playerPosition = $neighbourPosition;

        if (! $neighbourTile) {
            $this->generateTile($oldPosition, $this->playerPosition);
            // Maybe with an explorer card?
//            $neighbourTile = $this->tryTile($neighbourPosition);
        }

        // Maybe with an explorer card?
//        foreach ($neighbourTile->directions as $direction) {
//            $this->generateTile(from: $neighbourPosition, to: $neighbourPosition->getNeighbour($direction));
//        }

        $event = new PlayerMoved($oldPosition, $this->playerPosition);

        event($event);
    }

    public function placePlayer(Point $point): void
    {
        $oldPosition = $this->playerPosition;
        $this->playerPosition = $point;

        $event = new PlayerMoved($oldPosition, $this->playerPosition);

        event($event);
    }

    public function addCoinsToTile(Tile $tile, int $amount): void
    {
        if ($tile->coins >= $this->level->maxCoinCount()) {
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

        event(new TileCoinsCollected($tile, $collected, $this->coins));
    }

    public function increaseMaxMana(int $amount): void
    {
        $this->maxMana += $amount;

        event(new PlayerMaxManaIncreased($amount, $this->maxMana));
    }

    public function increaseMana(int $amount, ?string $reason = null): void
    {
        if ($this->mana >= $this->maxMana) {
            return;
        }

        if ($this->mana + $amount > $this->maxMana) {
            $overflow = $this->mana + $amount - $this->maxMana;
            $amount -= $overflow;
        }

        if ($amount <= 0) {
            return;
        }

        $this->mana += $amount;

        event(new PlayerManaIncreased($amount, $this->mana, $reason));
    }

    public function decreaseMana(int $amount): void
    {
        if ($this->mana - $amount < 0) {
            $overflow = -1 * ($this->mana - $amount);
            $amount -= $overflow;
        }

        if ($amount <= 0) {
            return;
        }

        $this->mana -= $amount;

        event(new PlayerManaDecreased($amount, $this->mana));
    }

    public function increaseCoins(int $amount): void
    {
        $this->coins += $amount;

        event(new PlayerCoinsIncreased($amount, $this->coins));
    }

    public function increaseMaxHealth(int $amount): void
    {
        $this->maxHealth += $amount;

        event(new PlayerMaxHealthIncreased($amount, $this->maxHealth));
    }

    public function increaseHealth(int $amount, ?string $reason = null): void
    {
        if ($this->health >= $this->maxHealth) {
            return;
        }

        if ($this->health + $amount > $this->maxHealth) {
            $overflow = $this->health + $amount - $this->maxHealth;
            $amount -= $overflow;
        }

        if ($amount <= 0) {
            return;
        }

        $this->health += $amount;

        event(new PlayerHealthIncreased($amount, $this->health, $reason));
    }

    public function decreaseHealth(int $amount, ?string $reason = null): void
    {
        if ($this->health - $amount < 0) {
            $overflow = -1 * ($this->health - $amount);
            $amount -= $overflow;
        }

        if ($amount <= 0) {
            return;
        }

        $this->health -= $amount;

        $event = new PlayerHealthDecreased($amount, $this->health, $reason);

        event($event);
    }

    public function decreaseStability(int $amount): void
    {
        if ($this->stability - $amount < 0) {
            $overflow = -1 * ($this->stability - $amount);
            $amount -= $overflow;
        }

        if ($amount <= 0) {
            return;
        }

        $this->stability -= $amount;

        if ($this->stability < 0) {
            $this->stability = 0;
        }

        event(new PlayerStabilityDecreased($amount, $this->stability));
    }

    public function increaseStability(int $amount, ?string $reason = null): void
    {
        if ($this->stability >= $this->maxStability) {
            return;
        }

        if ($this->stability + $amount > $this->maxStability) {
            $overflow = $this->stability + $amount - $this->maxStability;
            $amount -= $overflow;
        }

        if ($amount <= 0) {
            return;
        }

        $this->stability += $amount;

        event(new PlayerStabilityIncreased($amount, $this->stability, $reason));
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
            $this->unsetActiveCard();
        }

        if ($card->type->isPassive() && $this->passiveCard) {
            $this->unsetPassiveCard();
        }

        $this->decreaseMana($card->mana);

        $this->discard($card);

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
        if (! $this->passiveCard) {
            return;
        }

        $this->discard($this->passiveCard);

        event(new PassiveCardUnset());
    }

    public function setActiveCard(Card $card): void
    {
        $this->activeCard = $card;

        event(new ActiveCardSet($card));
    }

    public function unsetActiveCard(): void
    {
        if (! $this->activeCard) {
            return;
        }

        $this->discard($this->activeCard);

        event(new ActiveCardUnset());
    }

    public function addPermanentCard(Card $card): void
    {
        $this->permanentCards[$card->id] = $card;

        event(new PermanentCardAdded($card));
    }

    public function notifyCards(DungeonEvent $event): void
    {
        if ($this->activeCard instanceof PassiveCard) {
            $this->activeCard->handle($this, $event);
        }

        if ($this->passiveCard instanceof PassiveCard) {
            $this->passiveCard->handle($this, $event);
        }

        foreach ($this->permanentCards as $permanentCard) {
            if ($permanentCard instanceof PassiveCard) {
                $permanentCard->handle($this, $event);
            }
        }
    }

    public function interactWithTile(Point $point): void
    {
        if (! $this->withinVisibilityRadius($point)) {
            return;
        }

        $card = $this->activeCard;

        if (! $card instanceof ActiveCard) {
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
        $maxDistance = $this->level->maxDwellerDistance();

        $point ??= new Point(
            x: random_int(-1 * $maxDistance, $maxDistance),
            y: random_int(-1 * $maxDistance, $maxDistance),
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
        $max = $this->level->maxArtifactDistance();

        $tries = 25;

        while (! $point && $tries > 0) {
            $randomPoint = new Point(rand(-1 * $max, $max), rand(-1 * $max, $max));

            if ($this->tryTile($randomPoint)) {
                $tries--;
                continue;
            }

            if ($this->getLake($randomPoint)) {
                $tries--;
                continue;
            }

            $point = $randomPoint;
        }

        if ($point) {
            $this->artifactLocation = $point;
            event(new ArtifactSpawned($this->artifactLocation));
        }
    }

    public function spawnAltar(?Point $point = null): void
    {
        $methods = ['spawnManaAltar', 'spawnHealthAltar', 'spawnStabilityAltar'];

        $method = $methods[array_rand($methods)];

        $this->$method($point);
    }

    public function spawnManaAltar(?Point $point = null): void
    {
        $minDistance = $this->level->minAltarDistance();
        $maxDistance = $this->level->maxAltarDistance();

        $point ??= new Point(
            x: random_int(0, 1) ? random_int(-1 * $maxDistance, -1 * $minDistance) : random_int($minDistance, $maxDistance),
            y: random_int(0, 1) ? random_int(-1 * $maxDistance, -1 * $minDistance) : random_int($minDistance, $maxDistance),
        );

        $this->manaAltars[$point->x][$point->y] = $point;
    }

    public function spawnHealthAltar(?Point $point = null): void
    {
        $minDistance = $this->level->minAltarDistance();
        $maxDistance = $this->level->maxAltarDistance();

        $point ??= new Point(
            x: random_int(0, 1) ? random_int(-1 * $maxDistance, -1 * $minDistance) : random_int($minDistance, $maxDistance),
            y: random_int(0, 1) ? random_int(-1 * $maxDistance, -1 * $minDistance) : random_int($minDistance, $maxDistance),
        );

        $this->healthAltars[$point->x][$point->y] = $point;
    }

    public function spawnStabilityAltar(?Point $point = null): void
    {
        $minDistance = $this->level->minAltarDistance();
        $maxDistance = $this->level->maxAltarDistance();

        $point ??= new Point(
            x: random_int(0, 1) ? random_int(-1 * $maxDistance, -1 * $minDistance) : random_int($minDistance, $maxDistance),
            y: random_int(0, 1) ? random_int(-1 * $maxDistance, -1 * $minDistance) : random_int($minDistance, $maxDistance),
        );

        $this->stabilityAltars[$point->x][$point->y] = $point;
    }

    public function spawnVictoryPoint(?Point $point = null): void
    {
        $minDistance = $this->level->minTreasureDistance();
        $maxDistance = $this->level->maxTreasureDistance();

        $point ??= new Point(
            x: random_int(0, 1) ? random_int(-1 * $maxDistance, -1 * $minDistance) : random_int($minDistance, $maxDistance),
            y: random_int(0, 1) ? random_int(-1 * $maxDistance, -1 * $minDistance) : random_int($minDistance, $maxDistance),
        );

        $this->victoryPointLocations[$point->x][$point->y] = $point;
    }

    public function spawnShard(?Point $point = null): void
    {
        $minDistance = $this->level->minTreasureDistance();
        $maxDistance = $this->level->maxTreasureDistance();

        $point ??= new Point(
            x: random_int(0, 1) ? random_int(-1 * $maxDistance, -1 * $minDistance) : random_int($minDistance, $maxDistance),
            y: random_int(0, 1) ? random_int(-1 * $maxDistance, -1 * $minDistance) : random_int($minDistance, $maxDistance),
        );

        $this->shardLocations[$point->x][$point->y] = $point;
    }

    public function spawnLake(?Point $point = null): void
    {
        $shape = Lake::randomShape($this->level);

        if (! $shape) {
            return;
        }

        $minDistance = $this->level->minTreasureDistance();
        $maxDistance = $this->level->maxTreasureDistance();

        $point ??= new Point(
            x: random_int(0, 1) ? random_int(-1 * $maxDistance, -1 * $minDistance) : random_int($minDistance, $maxDistance),
            y: random_int(0, 1) ? random_int(-1 * $maxDistance, -1 * $minDistance) : random_int($minDistance, $maxDistance),
        );

        $lake = new Lake($point);

        $relicPoints = [];

        foreach ($shape as $yOffset => $row) {
            foreach ($row as $xOffset => $depth) {
                if ($depth === ' ') {
                    continue;
                }

                $depth = intval($depth);

                if ($depth === 0) {
                    $lake->addEdge($point->translate($xOffset, $yOffset));
                } else {
                    $lake->addLakePoint(new LakePoint(
                        point: $point->translate($xOffset, $yOffset),
                        depth: $depth,
                    ));
                }

                if ($depth === 3) {
                    $relicPoints[] = $point->translate($xOffset, $yOffset);
                }
            }
        }

        if ($relicPoints !== []) {
            $lake->relic = $relicPoints[array_rand($relicPoints)];
        }

        $this->lakes[] = $lake;
    }

    public function increaseExperience(int $amount): void
    {
        $this->experience += $amount;
    }

    public function collectArtifact(): void
    {
        if (! $this->playerPosition->equals($this->artifactLocation)) {
            return;
        }

        $artifactCoins = $this->level->artifactCoins();
        $variation = (int)round($artifactCoins * 0.1);
        $coins = random_int($artifactCoins - $variation, $artifactCoins + $variation);
        $this->increaseCoins($coins);
        $this->decreaseStability(random_int(10, 25));
        $this->increaseMana(random_int(5, 20));

        event(new ArtifactCollected($this->currentTile, $coins));

        $this->spawnArtifact();
    }

    public function collectRelic(Tile $tile): void
    {
        if (! $tile->isRelic) {
            return;
        }

        $relicCoins = $this->level->relicCoins();
        $variation = (int)round($relicCoins * 0.1);
        $coins = random_int($relicCoins - $variation, $relicCoins + $variation);
        $this->increaseCoins($coins);
        $this->increaseMana(random_int(20, 40));
        $tile->isRelic = false;

        event(new RelicCollected($tile, $coins));

        $this->spawnArtifact();
    }

    public function exit(bool $requiresOrigin = true): void
    {
        if ($requiresOrigin && ! $this->currentTile->isOrigin) {
            return;
        }

        $this->hasEnded = true;

        event(new PlayerExited(
            user: $this->user,
            coins: $this->coins,
            victoryPoints: $this->victoryPoints,
            shards: $this->shards,
            experience: $this->experience,
        ));
    }

    public function resign(): void
    {
        $this->hasEnded = true;

        event(new PlayerResigned($this->user));
    }

    public function updateTile(Tile $tile): void
    {
        event(new TileUpdated($tile));
    }

    public function increaseVictoryPoints(int $amount): void
    {
        $this->victoryPoints += $amount;

        event(new PlayerVictoryPointsIncreased($amount, $this->victoryPoints));
    }

    public function increaseShards(int $amount): void
    {
        $this->shards += $amount;

        event(new PlayerShardsIncreased($amount, $this->shards));

        $this->statsRepository->increaseStats($this->user, shards: 1);
    }

    public function updateCard(Card $card): void
    {
        event(new CardUpdated($card));
    }

    public function discard(Card $card): void
    {
        $this->discardedCards[$card->id] = $card;
        unset($this->hand[$card->id]);
        unset($this->deck[$card->id]);

        if ($this->activeCard && $this->activeCard->id === $card->id) {
            unset($this->activeCard);
        }

        if ($this->passiveCard && $this->passiveCard->id === $card->id) {
            unset($this->passiveCard);
        }
    }
}