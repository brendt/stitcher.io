<?php

use Stitcher\File;

return [
    'environment' => env('ENVIRONMENT'),
    'siteUrl' => 'https://www.stitcher.io',

    'plugins' => [
        \Brendt\Stitcher\Plugin\AppServiceProvider::class,
    ],

    'publicDirectory' => env('PUBLIC_DIRECTORY', File::path('public')),
    'sourceDirectory' => File::path('src'),
    'templateDirectory' => File::path('resources/view'),

    'configurationFile' => File::path('src/site.yaml'),

    'cacheImages' => env('CACHE_IMAGES', true),
    'cacheStaticFiles' => env('CACHE_STATIC', true),

    'staticFiles' => [
        'resources/img/static/',
        'resources/img/static/php-in-7-minutes-thumb.jpg',
        'resources/img/static/generics-thumb-1.png',
        'resources/img/static/generics-thumb-2.png',
        'resources/img/static/generics-thumb-3.png',
        'resources/img/static/generics-thumb-4.png',
        'resources/img/static/jb-thumb.png',
        'resources/img/favicon/',
        'resources/img/de-job-favicon/',
        'resources/img/meta.png',
        'resources/img/meta_small.png',
        'resources/img/rant-with-brent/logo.png',
        'resources/img/de-job/logo.png',
        'resources/fonts/Bangers',
        'resources/pwa',
    ],

    'errorPages' => [
        404 => 'errors/404.twig',
    ],

    'minify' => true,
];
