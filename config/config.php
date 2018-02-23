<?php

use Stitcher\File;

return [
    'environment' => env('ENVIRONMENT'),

    'plugins' => [
        \Brendt\Stitcher\Plugin\AppServiceProvider::class,
    ],

    'publicDirectory' => File::path('public/static'),
    'sourceDirectory' => File::path('src'),
    'templateDirectory' => File::path('resources/view'),

    'configurationFile' => File::path('src/site.yaml'),

    'cacheImages' => env('CACHE_IMAGES', true),
];
