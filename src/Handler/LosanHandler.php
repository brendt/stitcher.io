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

    private function response(string $path): Response
    {
        return new Response(
            200,
            [
                'Content-Type' => 'image/png',
                'Content-Length' => filesize($path),
                'Cache-Control' => 'max-age=3600',
            ],
            file_get_contents($path)
        );
    }
}
