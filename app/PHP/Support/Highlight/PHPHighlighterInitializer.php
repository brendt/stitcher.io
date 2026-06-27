<?php

namespace App\Php\Support\Highlight;

use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;
use Tempest\Highlight\Highlighter;
use Tempest\Highlight\Languages\Php\PhpLanguage;

final readonly class PHPHighlighterInitializer implements Initializer
{
    #[Singleton(tag: 'php')]
    public function initialize(Container $container): Highlighter
    {
        $highlighter = new Highlighter(fallbackLanguage: new PhpLanguage());

        $highlighter
            ->addLanguage(new RawLanguage())
            ->addLanguage(new ShellLanguage());

        return $highlighter;
    }
}