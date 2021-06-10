<?php

use Brendt\Stitcher\Handler\AddLikeHandler;
use Brendt\Stitcher\Handler\AllLikesHandler;
use Brendt\Stitcher\Handler\BlogsForDevsRssHandler;
use Brendt\Stitcher\Handler\GamesRssHandler;
use Brendt\Stitcher\Handler\GetLikesHandler;
use Brendt\Stitcher\Handler\MetaImageHandler;
use Brendt\Stitcher\Handler\PodcastsRssHandler;
use Brendt\Stitcher\Handler\RantWithBrentRssHandler;
use Brendt\Stitcher\Handler\BlogRssHandler;
use Brendt\Stitcher\Handler\RemoveLikeHandler;
use Stitcher\App;

$redirects = [
    '/blogs-for-devs' => '/blogs-for-devs/01-intro',
    '/feed' => '/rss',
    '/games' => '/games/all',
    '/podcasts' => '/podcasts/all',
    '/blog/laravel-domains' => '/blog/organise-by-domain',
    '/blog' => '/blog/page-1',
    '/presentations' => 'https://github.com/brendt/presentations',
    '/slides' => 'https://github.com/brendt/presentations',
    '/guide' => '/guide/setting-up',
    '/cognitive-load' => '/blog/a-programmers-cognitive-load',
    '/key-binding' => '/blog/mastering-key-bindings',
    '/key-bindings' => '/blog/mastering-key-bindings',
    '/keybinds' => '/blog/mastering-key-bindings',
    '/keybind' => '/blog/mastering-key-bindings',
    '/keybinding' => '/blog/mastering-key-bindings',
    '/keybindings' => '/blog/mastering-key-bindings',
    '/curly' => '/blog/where-a-curly-bracket-belongs',
    '/blog/array-merge-vs' => '/blog/array-merge-vs+',
    '/newsletter-signup' => '/newsletter/subscribe',
    '/signup' => '/newsletter/subscribe',

    '/laravel-beyond-crud' => '/blog/laravel-beyond-crud',
];

$router = App::router();

foreach ($redirects as $url => $targetUrl) {
    $router->redirect($url, $targetUrl);
    $router->redirect($url . '/', $targetUrl);
}

$newsLetters = [
    'https://mailchi.mp/7ee0ee7c848b/1-new-in-php',
];

foreach ($newsLetters as $i => $newsLetter) {
    $parts = explode('/', $newsLetter);

    $path = end($parts);

    $index = $i + 1;

    $router->redirect("/newsletter/{$path}", $newsLetter);
    $router->redirect("/newsletter/{$index}", $newsLetter);
}

$router->get('/img/meta/{slug}.png', MetaImageHandler::class);
$router->get('/blog/{slug}/meta', MetaImageHandler::class);
$router->get('/blogs-for-devs/{slug}/meta', MetaImageHandler::class);
$router->get('/img/meta/{slug}.png/nocache', MetaImageHandler::class);
$router->get('/rss', BlogRssHandler::class);
$router->get('/games/rss', GamesRssHandler::class);
$router->get('/podcasts/rss', PodcastsRssHandler::class);
$router->get('/blogs-for-devs/rss', BlogsForDevsRssHandler::class);
$router->get('/rss/rant-with-brent', RantWithBrentRssHandler::class);
$router->get('/likes.json', AllLikesHandler::class);
$router->post('/likes/delete/{slug}/{likeId}', RemoveLikeHandler::class);
$router->post('/likes/{slug}/{likeId}', AddLikeHandler::class);
$router->get('/likes/{slug}/{likeId}', GetLikesHandler::class);
