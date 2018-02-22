<?php

$redirects = [
    '/blog' => '/blog/page-1',
    '/cognitive-load' => '/blog/a-programmers-cognitive-load',
];

$router = \Stitcher\App::router();

foreach ($redirects as $url => $targetUrl) {
    $router->redirect($url, $targetUrl);
}
