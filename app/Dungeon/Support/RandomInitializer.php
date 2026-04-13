<?php

namespace App\Dungeon\Support;

use Random\Engine\Secure;
use Random\Randomizer;
use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;

final class RandomInitializer implements Initializer
{
    #[Singleton]
    public function initialize(Container $container): Random
    {
        return new RandomWithRandomizer(new Randomizer(new Secure()));
    }
}