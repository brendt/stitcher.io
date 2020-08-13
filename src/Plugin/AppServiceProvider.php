<?php

namespace Brendt\Stitcher\Plugin;

use Brendt\Stitcher\Plugin\Adapter\MetaAdapter;
use Brendt\Stitcher\Plugin\Adapter\NextAdapter;
use Brendt\Stitcher\Plugin\Markdown\AbbrParser;
use Brendt\Stitcher\Plugin\Markdown\AdRenderer;
use Brendt\Stitcher\Plugin\Adapter\GuideAdapter;
use Brendt\Stitcher\Plugin\Markdown\CodeRenderer;
use Brendt\Stitcher\Plugin\Markdown\HeadingAnchor;
use Brendt\Stitcher\Plugin\Markdown\HighlightCodeBlockRenderer;
use Brendt\Stitcher\Plugin\Markdown\HighlightInlineCodeRenderer;
use Brendt\Stitcher\Plugin\Markdown\ImageRenderer;
use Brendt\Stitcher\Plugin\Markdown\NumberParser;
use League\CommonMark\Block\Element\FencedCode;
use League\CommonMark\Block\Element\Heading;
use League\CommonMark\Block\Element\IndentedCode;
use League\CommonMark\Environment;
use League\CommonMark\Inline\Element\Code;
use League\CommonMark\Inline\Element\Image;
use League\CommonMark\Inline\Element\Text;
use Pageon\Html\Image\ImageFactory;
use Pageon\Lib\Markdown\MarkdownParser;
use Spatie\CommonMarkHighlighter\IndentedCodeRenderer;
use Stitcher\App;
use Stitcher\Page\Adapter\AdapterFactory;
use Stitcher\Plugin;

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
                ->addInlineRenderer(Text::class, new AdRenderer())
                ->addInlineRenderer(Code::class, new HighlightInlineCodeRenderer())
                ->addInlineRenderer(Image::class, new ImageRenderer($imageFactory))
                ->addInlineParser(new AbbrParser())
                ->addInlineParser(new NumberParser())
                ->addBlockRenderer(Heading::class, new HeadingAnchor())
                ->addBlockRenderer(FencedCode::class, new HighlightCodeBlockRenderer())
                ->addBlockRenderer(IndentedCode::class, new IndentedCodeRenderer());
        });

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
