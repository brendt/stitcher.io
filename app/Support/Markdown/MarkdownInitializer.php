<?php

namespace App\Support\Markdown;

use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;
use Tempest\Highlight\Highlighter;
use Tempest\Markdown\Markdown;
use Tempest\ResponsiveImage\ResponsiveImageFactory;

final readonly class MarkdownInitializer implements Initializer
{
    #[Singleton]
    public function initialize(Container $container): Markdown
    {
        return new Markdown(
            highlighter: $container->get(Highlighter::class),
            imageFactory: $container->get(ResponsiveImageFactory::class),
        );
    }
}