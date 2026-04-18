<?php

namespace App\Dungeon\Support;

use App\Dungeon\Dungeon;
use App\Dungeon\Repositories\DungeonRepository;
use App\Support\Authentication\User;
use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;

final class DungeonInitializer implements Initializer
{
    #[Singleton]
    public function initialize(Container $container): Dungeon
    {
        $repository = $container->get(DungeonRepository::class);

        $user = $container->get(User::class);

        $dungeon = $repository->forUser($user);

        if (! $dungeon) {
            $dungeon = new Dungeon();
            $dungeon->user = $user;
            $repository->persist($dungeon);
        }

        return $dungeon;
    }
}