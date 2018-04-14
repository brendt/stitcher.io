<?php

use Brendt\Stitcher\Handler\RssHandler;

$redirects = [
    '/blog' => '/blog/page-1',
    '/guide' => '/guide/setting-up',
    '/cognitive-load' => '/blog/a-programmers-cognitive-load',
];

$router = \Stitcher\App::router();

foreach ($redirects as $url => $targetUrl) {
    $router->redirect($url, $targetUrl);
}

$router->get('/rss', RssHandler::class);
