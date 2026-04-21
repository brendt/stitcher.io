<?php

namespace App\Dungeon\Listeners;

use App\Dungeon\Dungeon;
use App\Dungeon\Events\PlayerMoved;
use App\Dungeon\Events\TileCollapsed;
use App\Dungeon\Support\Random;
use Tempest\EventBus\EventHandler;
use function Tempest\Support\arr;

final readonly class PlayerMovementListener
{
    public function __construct(
        private Dungeon $dungeon,
        private Random $random,
    ) {}

    #[EventHandler]
    public function spawnCoins(PlayerMoved $event): void
    {
        foreach ($this->dungeon->loopTiles() as $tile) {
            if ($this->random->chance(199 / 200)) {
                continue;
            }

            $this->dungeon->addCoinsToTile($tile, random_int(1, 3));

            return;
        }
    }

    #[EventHandler]
    public function collectCoins(PlayerMoved $event): void
    {
        $tile = $this->dungeon->tryTile($event->to);

        if (! $tile) {
            return;
        }

        if ($tile->coins === 0) {
            return;
        }

        $this->dungeon->collectCoins($tile);
    }

    #[EventHandler]
    public function gainMana(PlayerMoved $event): void
    {
        $amount = arr([0, 0, 0, 1, 1, 1, 1, 1, 2, 2, 2, 5])->random();

        $this->dungeon->increaseMana($amount);
    }

    #[EventHandler]
    public function decreaseStability(PlayerMoved $event): void
    {
        $tileCount = $this->dungeon->tileCount();

        $amount = match (true) {
            $tileCount > 700 => arr([
                0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 1, 1, 2, 2
            ])->random(),
            $tileCount > 400 => arr([
                0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 1
            ])->random(),
            $tileCount > 100 => arr([
                0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 1
            ])->random(),
            default => arr([
                0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 1
            ])->random(),
        };

        $this->dungeon->decreaseStability($amount);
    }

    #[EventHandler]
    public function collapseTile(PlayerMoved $event): void
    {
        $chanceToCollapse = match (true) {
            $this->dungeon->stability === 0 => 6,
            $this->dungeon->stability < 10 => 3,
            $this->dungeon->stability < 30 => 2,
            $this->dungeon->stability < 60 => 1,
            default => 0,
        };

        foreach ($this->dungeon->loopTiles() as $tile) {
            if (! $tile->canCollapse()) {
                continue;
            }

            if (rand(1, 1000) > $chanceToCollapse) {
                continue;
            }

            $this->dungeon->collapseTile($tile);
        }
    }

    #[EventHandler]
    public function onTileCollapsed(TileCollapsed $event): void
    {
        if ($event->tile->point->equals($this->dungeon->playerPosition))
        {
            $this->dungeon->decreaseHealth(
                25,
                'The tile you were standing on collapsed! (-25 health)',
            );
        }
    }

    #[EventHandler]
    public function collectArtifact(PlayerMoved $event): void
    {
        if (! $this->dungeon->artifactLocation) {
            return;
        }

        if ($event->to->equals($this->dungeon->artifactLocation)) {
            $this->dungeon->collectArtifact();
        }
    }

    #[EventHandler]
    public function checkForTraps(PlayerMoved $event): void
    {
        $tile = $this->dungeon->tryTile($event->to);

        if (! $tile) {
            return;
        }

        if (! $tile->isTrapped) {
            return;
        }

        $this->dungeon->decreaseHealth(15, 'You stepped on a trap! (-15 health)');
    }

    #[EventHandler]
    public function checkForManaAltar(PlayerMoved $event): void
    {
        $tile = $this->dungeon->tryTile($event->to);

        if (! $tile) {
            return;
        }

        if (! $tile->isManaAltar) {
            return;
        }

        if ($tile->altarCooldown > 0) {
            return;
        }

        $tile->altarCooldown = random_int(80, 120);
        $this->dungeon->updateTile($tile);
        $mana = random_int(50, 100);
        $this->dungeon->increaseMana($mana, "You found a mana altar (+{$mana} mana)");
    }

    #[EventHandler]
    public function checkForHealthAltar(PlayerMoved $event): void
    {
        $tile = $this->dungeon->tryTile($event->to);

        if (! $tile) {
            return;
        }

        if (! $tile->isHealthAltar) {
            return;
        }

        if ($tile->altarCooldown > 0) {
             return;
        }

        $tile->altarCooldown = random_int(80, 120);
        $this->dungeon->updateTile($tile);
        $health = random_int(30, 50);
        $this->dungeon->increaseHealth($health, "You found a health altar (+{$health} health)");
    }

    #[EventHandler]
    public function checkForStabilityAltar(PlayerMoved $event): void
    {
        $tile = $this->dungeon->tryTile($event->to);

        if (! $tile) {
            return;
        }

        if (! $tile->isStabilityAltar) {
            return;
        }

        if ($tile->altarCooldown > 0) {
            return;
        }

        $tile->altarCooldown = random_int(80, 120);
        $this->dungeon->updateTile($tile);
        $stability = random_int(30, 50);
        $this->dungeon->increaseStability($stability, "You found a stability altar (+{$stability} stability)");
    }

    #[EventHandler]
    public function checkForShard(PlayerMoved $event): void
    {
        $tile = $this->dungeon->tryTile($event->to);

        if (! $tile) {
            return;
        }

        if (! $tile->isShard) {
            return;
        }

        if ($tile->isShardCollected) {
            return;
        }

        $tile->isShardCollected = true;
        $this->dungeon->updateTile($tile);
        $this->dungeon->increaseShards(1);
    }

    #[EventHandler]
    public function checkForVictoryPoint(PlayerMoved $event): void
    {
        $tile = $this->dungeon->tryTile($event->to);

        if (! $tile) {
            return;
        }

        if (! $tile->isVictoryPoint) {
            return;
        }

        if ($tile->isVictoryPointCollected) {
            return;
        }

        $tile->isVictoryPointCollected = true;
        $this->dungeon->updateTile($tile);
        $this->dungeon->increaseVictoryPoints(1);
    }

    #[EventHandler]
    public function handleAltarCooldowns(PlayerMoved $event): void
    {
        foreach ($this->dungeon->loopTiles() as $tile) {
            if ($tile->altarCooldown > 0) {
                $tile->altarCooldown -= 1;
                $this->dungeon->updateTile($tile);
            }
        }
    }
}