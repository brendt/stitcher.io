<?php

use Pageon\Config;
use Stitcher\App;
use Stitcher\File;

require_once __DIR__ . '/../vendor/autoload.php';

File::base(__DIR__ . '/../');

App::init();

if ('local' === Config::get('environment')) {
    $server = App::developmentServer();
} else {
    $server = App::productionServer();
}

echo $server->run();
