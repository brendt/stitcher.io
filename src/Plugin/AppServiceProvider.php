<?php

namespace Brendt\Stitcher\Plugin;

use Brendt\Stitcher\Plugin\Adapter\NextAdapter;
use Brendt\Stitcher\Plugin\Markdown\AdRenderer;
use Brendt\Stitcher\Plugin\Adapter\GuideAdapter;
use League\CommonMark\Block\Element\FencedCode;
use League\CommonMark\Block\Element\IndentedCode;
use League\CommonMark\Environment;
use League\CommonMark\Inline\Element\Text;
use Pageon\Lib\Markdown\MarkdownParser;
use Spatie\CommonMarkHighlighter\FencedCodeRenderer;
use Spatie\CommonMarkHighlighter\IndentedCodeRenderer;
use Stitcher\App;
use Stitcher\Page\Adapter\AdapterFactory;
use Stitcher\Plugin;
use Stitcher\Variable\VariableParser;

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
            return $environment
                ->addInlineRenderer(Text::class, new AdRenderer())
                ->addBlockRenderer(FencedCode::class, new FencedCodeRenderer())
                ->addBlockRenderer(IndentedCode::class, new IndentedCodeRenderer());
        });

        /** @var AdapterFactory $adapterFactory */
        $adapterFactory = App::get(AdapterFactory::class);

        $adapterFactory->setRule(
            NextAdapter::class,
            function (string $adapterType, array $adapterConfiguration) {
                if ($adapterType !== 'next') {
                    return null;
                }

                return new NextAdapter();
            }
        );
    }
}
