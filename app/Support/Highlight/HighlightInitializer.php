<?php

namespace App\Support\Highlight;

use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;
use Tempest\Highlight\Highlighter;

final readonly class HighlightInitializer implements Initializer
{
    #[Singleton]
    public function initialize(Container $container): Highlighter
    {
        $highlighter = new Highlighter();

        $highlighter
            ->addLanguage(new ExtendedPhpLanguage())
            ->addLanguage(new TempestConsoleWebLanguage())
            ->addLanguage(new TempestViewLanguage());

        return $highlighter;
    }
}