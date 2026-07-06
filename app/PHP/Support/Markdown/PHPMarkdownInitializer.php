<?php

namespace App\PHP\Support\Markdown;

use App\Support\Markdown\SnippetRule;
use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;
use Tempest\Highlight\Highlighter;
use Tempest\Markdown\Markdown;
use Tempest\Markdown\Rules\PreRule as TempestPreRule;
use Tempest\ResponsiveImage\ResponsiveImageFactory;

final readonly class PHPMarkdownInitializer implements Initializer
{
    #[Singleton(tag: 'php')]
    public function initialize(Container $container): Markdown
    {
        $markdown = new Markdown(
            highlighter: $container->get(Highlighter::class, tag: 'php'),
            imageFactory: $container->get(ResponsiveImageFactory::class),
        );

        $markdown->removeRules(TempestPreRule::class);
        $markdown->prependRules(
            new SummaryRule(),
            $container->get(PreRule::class),
            new SnippetRule(__DIR__ . '/../../Snippets/'),
        );

        return $markdown;
    }
}
