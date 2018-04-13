<?php

use Stitcher\App;
use Stitcher\File;

require_once __DIR__ . '/vendor/autoload.php';

File::base(__DIR__ . '/');

App::init();

App::get('parse')->execute();
