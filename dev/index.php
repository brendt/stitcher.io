<?php

require_once '../vendor/autoload.php';

use brendt\stitcher\controller\DevController;

// This controller will render HTML pages on the fly.
// See config.dev.yml for more information.
$controller = new DevController(__DIR__ . '/../dev', 'config.dev.yml');
echo $controller->run();
