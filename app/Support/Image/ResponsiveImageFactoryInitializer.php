<?php

namespace App\Support\Image;

use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;
use Tempest\ResponsiveImage\ResponsiveImageConfig;
use Tempest\ResponsiveImage\ResponsiveImageFactory;

use function Tempest\root_path;
use function Tempest\src_path;

final readonly class ResponsiveImageFactoryInitializer implements Initializer
{
    #[Singleton]
    public function initialize(Container $container): ResponsiveImageFactory
    {
        return new ResponsiveImageFactory(
            new ResponsiveImageConfig(
                srcPath: src_path('/Blog/'),
                publicPath: root_path('public'),
            ),
        );
    }
}
