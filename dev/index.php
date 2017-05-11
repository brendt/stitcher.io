<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Brendt\Stitcher\App;

// This index file will render HTML pages on the fly.
// See config.dev.yml for more information.
echo App::init('./config.dev.yml')::get('app.dev.controller')->run();
