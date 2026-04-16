<?php

namespace App\Dungeon\Listeners;

use App\Dungeon\Dungeon;
use App\Dungeon\Events\PlayerMoved;
use App\Dungeon\Events\TileUpdated;
use App\Dungeon\Support\Random;
use Tempest\EventBus\EventHandler;
use function Tempest\EventBus\event;
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

            $this->dungeon->addCoinsToTile($tile, 1);

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

        $this->dungeon->decreaseHealth(15);
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

        $tile->altarCooldown = random_int(80, 120);
        $this->dungeon->updateTile($tile);
        $this->dungeon->increaseMana(random_int(50, 100));
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

        $tile->altarCooldown = random_int(80, 120);
        $this->dungeon->updateTile($tile);
        $this->dungeon->increaseHealth(random_int(30, 50));
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

        $tile->altarCooldown = random_int(80, 120);
        $this->dungeon->updateTile($tile);
        $this->dungeon->increaseStability(random_int(30, 50));
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