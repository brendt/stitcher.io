<?php

use Pageon\Config;
use Stitcher\App;
use Stitcher\File;
use Tempest\Framework\Tempest;

require_once __DIR__ . '/../vendor/autoload.php';

error_reporting(E_ALL ^ E_DEPRECATED);

$uri = $_SERVER['REQUEST_URI'] ?? '';

if (str_starts_with($uri, '/app')) {
    Tempest::boot(__DIR__ . '/../')->http()->run();
} else {
    File::base(__DIR__ . '/../');

    App::init();

    if ('local' === Config::get('environment')) {
        $server = App::developmentServer();
    } else {
        $server = App::productionServer();
    }

    echo $server->run();
}
