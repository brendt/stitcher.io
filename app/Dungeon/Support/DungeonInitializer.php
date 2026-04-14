<?php

namespace App\Dungeon\Support;

use App\Dungeon\Dungeon;
use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;

final class DungeonInitializer implements Initializer
{
    #[Singleton]
    public function initialize(Container $container): Dungeon
    {
        $repository = $container->get(DungeonRepository::class);

        $dungeon = $repository->get();

        if (!$dungeon) {
            $dungeon = new Dungeon();
            $repository->persist($dungeon);
        }

        return $dungeon;
    }
}