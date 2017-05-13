<?php

namespace Brendt\Stitcher\Plugin;

use Brendt\Stitcher\App;
use Brendt\Stitcher\Factory\AdapterFactory;

class PortfolioPlugin implements Plugin {

    public function __construct() {
        /** @var AdapterFactory $adapterFactory */
        $adapterFactory = App::get('factory.adapter');

        $adapterFactory->addAdapter('guide', function () {
            return App::get('adapter.guide');
        });
    }

    /**
     * Get the location of your plugin's `config.yml` file.
     *
     * @return null|string
     */
    public function getConfigPath() {
        return;
    }

    /**
     * Get the location of your plugin's `services.yml` file.
     *
     * @return null|string
     */
    public function getServicesPath() {
        return __DIR__ . '/../../../../config/services.portfolio.yml';
    }
}
