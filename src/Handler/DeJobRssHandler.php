<?php

namespace Brendt\Stitcher\Handler;

use GuzzleHttp\Psr7\Response;
use Stitcher\App;
use Stitcher\File;
use Stitcher\Renderer\RendererFactory;
use Stitcher\Variable\VariableParser;
use Stitcher\Variable\YamlVariable;
use Symfony\Component\Yaml\Yaml;

class DeJobRssHandler
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
        $items = YamlVariable::make(
            File::path('src/content/de-job.yaml'),
            App::get(Yaml::class),
            App::get(VariableParser::class)
        )->getParsed();

        $rss = $this->renderer->renderTemplate(
            'rss-podcast.twig',
            [
                'items' => $items,
                'image' => 'https://stitcher.io/resources/img/de-job/logo.png',
                'podcastTitle' => 'De Job',
                'podcastDescription' => 'Het waargebeurde verhaal over mijn eerste jaren in het echte werkleven',
            ]
        );

        return new Response(200, ['Content-Type' => 'application/xml;charset=UTF-8'], $rss);
    }
}
