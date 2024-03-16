<?php

namespace Brendt\Stitcher\Plugin;

use Brendt\Stitcher\Plugin\Adapter\MetaAdapter;
use Brendt\Stitcher\Plugin\Adapter\NextAdapter;
use Brendt\Stitcher\Plugin\Markdown\AbbrParser;
use Brendt\Stitcher\Plugin\Markdown\AdRenderer;
use Brendt\Stitcher\Plugin\Markdown\HeadingAnchor;
use Brendt\Stitcher\Plugin\Markdown\HighlightCodeBlockRenderer;
use Brendt\Stitcher\Plugin\Markdown\HighlightInlineCodeRenderer;
use Brendt\Stitcher\Plugin\Markdown\ImageRenderer;
use Brendt\Stitcher\Plugin\Markdown\LinkRenderer;
use Brendt\Stitcher\Plugin\Markdown\NumberParser;
use Brendt\Stitcher\Plugin\Twig\ImageExtension;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\Node\Block\FencedCode;
use League\CommonMark\Extension\CommonMark\Node\Block\Heading;
use League\CommonMark\Extension\CommonMark\Node\Block\IndentedCode;
use League\CommonMark\Extension\CommonMark\Node\Inline\Code;
use League\CommonMark\Extension\CommonMark\Node\Inline\Image;
use League\CommonMark\Extension\CommonMark\Node\Inline\Link;
use League\CommonMark\Extension\CommonMark\Renderer\Block\IndentedCodeRenderer;
use League\CommonMark\Node\Inline\Text;
use Pageon\Html\Image\ImageFactory;
use Pageon\Lib\Markdown\MarkdownParser;
use Stitcher\App;
use Stitcher\Page\Adapter\AdapterFactory;
use Stitcher\Plugin;
use Stitcher\Renderer\RendererFactory;

class AppServiceProvider implements Plugin
{
    public static function getServicesPath(): string
    {
        return __DIR__ . '/../services.yaml';
    }

    public static function getConfigurationPath(): ?string
    {
        return null;
    }

    public static function boot(): void
    {
        MarkdownParser::extension(function (Environment $environment) {
            $imageFactory = App::get(ImageFactory::class);

            return $environment
                ->addRenderer(Link::class, new LinkRenderer())
                ->addRenderer(Text::class, new AdRenderer())
                ->addRenderer(Code::class, new HighlightInlineCodeRenderer())
                ->addRenderer(Image::class, new ImageRenderer($imageFactory))
                ->addRenderer(Heading::class, new HeadingAnchor())
                ->addRenderer(FencedCode::class, new HighlightCodeBlockRenderer())
                ->addRenderer(IndentedCode::class, new IndentedCodeRenderer());
        });

//        /** @var \Stitcher\Renderer\RendererFactory $rendererFactory */
        App::get(RendererFactory::class)
            ->addExtension(new ImageExtension(App::get(ImageFactory::class)));
//
//        $rendererFactory->addExtension(new Production());

        /** @var AdapterFactory $adapterFactory */
        $adapterFactory = App::get(AdapterFactory::class);

        $adapterFactory
            ->setRule(
                NextAdapter::class,
                function (string $adapterType, array $adapterConfiguration) {
                    if ($adapterType !== 'next') {
                        return null;
                    }

                    return new NextAdapter();
                }
            )
            ->setRule(
                MetaAdapter::class,
                function (string $adapterType, array $adapterConfiguration) {
                    if ($adapterType !== 'meta') {
                        return null;
                    }

                    return new MetaAdapter();
                }
            );
    }
}
