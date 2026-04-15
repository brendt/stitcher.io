<?php

namespace App\Dungeon\Listeners;

use App\Dungeon\Commands\ChangeStability;
use App\Dungeon\Commands\CollapseTile;
use App\Dungeon\Commands\RemoveDweller;
use App\Dungeon\Dungeon;
use App\Dungeon\Dweller;
use App\Dungeon\Events\DwellerMoved;
use App\Dungeon\Events\PlayerMoved;
use App\Dungeon\Point;
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
        if ($event->to->equals($this->dungeon->artifactLocation)) {
            $this->dungeon->collectArtifact();
        }
    }
}