<?php

use Stitcher\App;
use Stitcher\File;
use Stitcher\Renderer\RendererFactory;
use Stitcher\Variable\VariableParser;
use Stitcher\Variable\YamlVariable;
use Symfony\Component\Yaml\Yaml;

require_once __DIR__ . '/vendor/autoload.php';

error_reporting(E_ALL ^ E_DEPRECATED);

File::base(__DIR__ . '/');

App::init();

$posts = YamlVariable::make(
    File::path('src/content/blog.yaml'),
    App::get(Yaml::class),
    App::get(VariableParser::class)
)->getParsed();

$rendererFactory = App::get(RendererFactory::class);

$renderer = $rendererFactory->create();

$rss = $renderer->renderTemplate(
    'rss.twig',
    ['posts' => $posts]
);

file_put_contents(__DIR__ . '/public/rss.xml', $rss);
