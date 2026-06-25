<?php

namespace App\PHP\Support;

use App\PHP\Support\Highlight\ShellLanguage;
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
            ->addLanguage(new ShellLanguage());

        return $highlighter;
    }
}