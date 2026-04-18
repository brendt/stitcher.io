<?php

namespace App\Dungeon\Listeners;

use App\Dungeon\Events\PlayerExited;
use App\Dungeon\Repositories\StatsRepository;
use Tempest\EventBus\EventHandler;

final class StatsListeners
{
    public function __construct(
        private readonly StatsRepository $statsRepository,
    ) {}

    #[EventHandler]
    public function increaseOnExit(PlayerExited $event): void
    {
        $this->statsRepository->increaseStats(
            user: $event->user,
            coins: $event->coins,
            experience: $event->experience,
            victoryPoints: $event->victoryPoints + 1,
        );
    }
}