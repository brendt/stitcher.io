<?php

namespace Brendt\Stitcher\Handler;

use GuzzleHttp\Psr7\Response;
use Stitcher\App;
use Stitcher\File;
use Stitcher\Renderer\RendererFactory;
use Stitcher\Variable\VariableParser;
use Stitcher\Variable\YamlVariable;
use Symfony\Component\Yaml\Yaml;

abstract class RssHandler
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
        $rss = file_get_contents(__DIR__ . '/../../public/rss.xml');

        return new Response(200, ['Content-Type' => 'application/xml;charset=UTF-8'], $rss);
    }

    protected function getTemplatePath(): string
    {
        return 'rss.twig';
    }

    abstract protected function getSourcePath(): string;
}
