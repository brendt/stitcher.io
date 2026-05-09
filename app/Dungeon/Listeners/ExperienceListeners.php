<?php

namespace App\Dungeon\Listeners;

use App\Dungeon\Dungeon;
use App\Dungeon\Events\ArtifactCollected;
use App\Dungeon\Events\TileGenerated;
use Tempest\EventBus\EventHandler;

final readonly class ExperienceListeners
{
    public function __construct(
        private Dungeon $dungeon,
    ) {}

    #[EventHandler]
    public function experienceForGeneratedTiles(TileGenerated $event): void
    {
        $tileCount = $this->dungeon->tileCount();

        if ($tileCount % 20) {
            $this->dungeon->increaseExperience(1);
        }
    }

    public function experienceForArtifactCollected(ArtifactCollected $event): void
    {
        $this->dungeon->increaseExperience(20);
    }
}