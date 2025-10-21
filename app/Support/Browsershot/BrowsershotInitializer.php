<?php

namespace App\Support\Browsershot;

use Spatie\Browsershot\Browsershot;
use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;
use function Tempest\env;

final readonly class BrowsershotInitializer implements Initializer
{
    #[Singleton(tag: 'meta')]
    public function initialize(Container $container): Browsershot
    {
        $browsershot = new Browsershot()
            ->setOption('args', ['--disable-web-security'])
            ->windowSize(1200, 628)
            ->deviceScaleFactor(2);

        if ($includePath = env('BROWSERSHOT_PATH')) {
            $browsershot->setIncludePath("{$includePath}:\$PAT:");
        }

        if ($nodePath = env('BROWSERSHOT_NODE_PATH')) {
            $browsershot->setNodeBinary($nodePath);
        }

        if ($npmPath = env('BROWSERSHOT_NPM_PATH')) {
            $browsershot->setNpmBinary($npmPath);
        }

        return $browsershot;
    }
}