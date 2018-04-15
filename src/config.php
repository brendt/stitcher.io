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
        'resources/img/favicon/',
        'resources/img/meta.png',
        'resources/img/meta',
    ],
];
