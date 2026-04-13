<?php

namespace App\Dungeon\Listeners;

use App\Dungeon\Dungeon;
use App\Dungeon\Events\PlayerMoved;
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
        $coinsAdded = 0;

        foreach ($this->dungeon->loopTiles() as $tile) {
            if ($coinsAdded > 3) {
                break;
            }

            if ($this->random->chance(199 / 200)) {
                continue;
            }

            if ($tile->coins >= 10) {
                continue;
            }

            $this->dungeon->addCoinsToTile($tile, 1);
            $coinsAdded += 1;
        }
    }

    #[EventHandler]
    public function collectCoins(PlayerMoved $event): void
    {
        $tile = $this->dungeon->getTile($event->to);

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

        $this->dungeon->gainMana($amount);
    }
}