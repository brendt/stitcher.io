<?php

namespace App\Dungeon\Listeners;

use App\Dungeon\Dungeon;
use App\Dungeon\Events\PlayerDied;
use App\Dungeon\Events\PlayerHealthDecreased;
use Tempest\EventBus\EventHandler;
use function Tempest\EventBus\event;

final readonly class HealthListeners
{
    public function __construct(
        private Dungeon $dungeon,
    ) {}

    #[EventHandler]
    public function checkForDeath(PlayerHealthDecreased $event): void
    {
        if ($this->dungeon->health > 0) {
            return;
        }

        $this->dungeon->hasEnded = true;

        event(new PlayerDied($this->dungeon->user));
    }
}