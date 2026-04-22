<?php

namespace App\Dungeon\Listeners;

use App\Dungeon\Dungeon;
use App\Dungeon\Events\LakeDiscovered;
use Tempest\EventBus\EventHandler;

final readonly class LakeListeners
{
    public function __construct(
        private Dungeon $dungeon,
    ) {}

    #[EventHandler]
    public function generatedLakeWhenDiscovered(LakeDiscovered $event): void
    {
        foreach ($event->lake->loopLakePoints() as $lakePoint) {
            $this->dungeon->generateTile(from: null, to: $lakePoint->point);
        }
    }
}