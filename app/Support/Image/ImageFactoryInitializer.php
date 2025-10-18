<?php

namespace App\Support\Image;

use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;

final readonly class ImageFactoryInitializer implements Initializer
{
    #[Singleton]
    public function initialize(Container $container): ImageFactory
    {
        return new ImageFactory(
            new ImageManager(driver: Driver::class),
        );
    }
}