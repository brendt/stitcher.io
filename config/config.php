<?php

use Stitcher\File;

return [
    'environment' => env('ENVIRONMENT'),

    'publicDirectory' => File::path('public/static'),
    'sourceDirectory' => File::path('src'),
    'templateDirectory' => File::path('resources/view'),

    'configurationFile' => File::path('src/site.yaml'),
];
