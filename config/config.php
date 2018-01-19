<?php

use Stitcher\File;

return [
    'environment' => env('ENVIRONMENT'),

    'publicDirectory' => File::path('public'),
    'sourceDirectory' => File::path('src'),
    'templateDirectory' => File::path('resources/view'),

    'configurationFile' => File::path('src/site.yaml'),
];
