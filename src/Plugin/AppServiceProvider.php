<?php

namespace Brendt\Stitcher\Plugin;

use Brendt\Stitcher\Plugin\Portfolio\GuideAdapter;
use Stitcher\App;
use Stitcher\Page\Adapter\AdapterFactory;
use Stitcher\Plugin;
use Stitcher\Variable\VariableParser;

class AppServiceProvider implements Plugin
{
    public static function getServicesPath(): string
    {
        return __DIR__ . '/../services.yaml';
    }

    public static function getConfigurationPath(): ?string
    {
        return null;
    }

    public static function boot(): void
    {
        /** @var AdapterFactory $adapterFactory */
        $adapterFactory = App::get(AdapterFactory::class);

        $adapterFactory->setRule(
            GuideAdapter::class,
            function (string $adapterType, array $adapterConfiguration) {
                if ($adapterType !== 'guide') {
                    return null;
                }

                return new GuideAdapter($adapterConfiguration, App::get(VariableParser::class));
            }
        );
    }
}
