<?php

namespace App\Support\Markdown;

use App\Support\Highlight\HighlightCodeBlockRenderer;
use App\Support\Highlight\HighlightInlineCodeRenderer;
use App\Support\Image\ImageRenderer;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\Attributes\AttributesExtension;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\CommonMark\Node\Inline\Image;
use League\CommonMark\Extension\FrontMatter\FrontMatterExtension;
use League\CommonMark\MarkdownConverter;
use League\CommonMark\Node\Block\Paragraph;
use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;

final readonly class LeagueMarkdownInitializer implements Initializer
{
    #[Singleton]
    public function initialize(Container $container): MarkdownConverter
    {
        $environment = new Environment();

        $environment
            ->addExtension(new CommonMarkCoreExtension())
            ->addExtension(new FrontMatterExtension())
            ->addExtension(new AttributesExtension())
            ->addRenderer(Paragraph::class, $container->get(SnippetRenderer::class))
            ->addRenderer(Image::class, $container->get(ImageRenderer::class))
        ;

        return new MarkdownConverter($environment);
    }
}
