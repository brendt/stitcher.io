<?php

namespace App\Dungeon\Listeners;

use App\Dungeon\Dungeon;
use App\Dungeon\Events\TileGenerated;
use Tempest\EventBus\EventHandler;
use function Tempest\Support\arr;

final readonly class TileGenerationListener
{
    public function __construct(
        private Dungeon $dungeon,
    ) {}

    #[EventHandler]
    public function decreaseStability(TileGenerated $tileGenerated): void
    {
        if ($tileGenerated->tile->isOrigin) {
            return;
        }

        $tileCount = $this->dungeon->tileCount();

        $decrease = match (true) {
            $tileCount > 700 => arr([
                0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 1, 1, 2, 2, 3, 3
            ])->random(),
            $tileCount > 400 => arr([
                0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 1, 1, 2
            ])->random(),
            $tileCount > 100 => arr([
                0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 1, 2
            ])->random(),
            default => arr([
                0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 1, 2
            ])->random(),
        };

        $this->dungeon->decreaseStability($decrease);
    }
}