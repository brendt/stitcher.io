<?php

namespace Brendt\Stitcher\Handler;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Pageon\Config;

class LosanHandler
{
    public function handle(Request $request, $name): Response
    {
        $similarity = levenshtein(strtolower(Config::get('losan')), strtolower($name));

        echo "<h1>{$similarity}</h1>";

        exit;
    }
}
