<?php

namespace Brendt\Stitcher\Handler;

use GuzzleHttp\Psr7\Response;
use Pageon\Http\Header;
use Stitcher\App;
use Stitcher\File;
use Stitcher\Renderer\RendererFactory;
use Stitcher\Variable\VariableParser;
use Stitcher\Variable\YamlVariable;
use Symfony\Component\Yaml\Yaml;

class RssHandler
{
    /** @var \Stitcher\Renderer\Renderer */
    private $renderer;

    public function __construct()
    {
        /** @var RendererFactory $rendererFactory */
        $rendererFactory = App::get(RendererFactory::class);

        $this->renderer = $rendererFactory->create();
    }

    public function handle(): Response
    {
        $posts = YamlVariable::make(
            File::path('src/content/blog.yaml'),
            App::get(Yaml::class),
            App::get(VariableParser::class)
        )->getParsed();

        $rss = $this->renderer->renderTemplate(
            'rss.twig',
            ['posts' => $posts]
        );

        return new Response(200, ['Content-Type' => 'application/xml;charset=UTF-8'], $rss);
    }
}
