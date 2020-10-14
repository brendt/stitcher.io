<?php

require_once __DIR__ . '/vendor/autoload.php';

use Stitcher\App;
use Stitcher\File;

File::base(__DIR__ . '/');

App::init();

$console = new \Brendt\Stitcher\Console\App();

$console->run();
