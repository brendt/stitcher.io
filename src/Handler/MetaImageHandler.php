<?php

namespace Brendt\Stitcher\Handler;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Spatie\Browsershot\Browsershot;
use Stitcher\App;
use Stitcher\File;
use Stitcher\Renderer\Renderer;
use Stitcher\Renderer\RendererFactory;
use Symfony\Component\Yaml\Yaml;

class MetaImageHandler
{
    private Renderer $renderer;

    public function __construct()
    {
        $rendererFactory = App::get(RendererFactory::class);

        $this->renderer = $rendererFactory->create();
    }

    public function handle(Request $request, $slug): Response
    {
        /** @var \Symfony\Component\Yaml\Yaml $yaml */
        $yaml = App::get(Yaml::class);

        $post = $yaml->parse(file_get_contents(File::path('src/content/blog.yaml')))[$slug]
            ?? $yaml->parse(file_get_contents(File::path('src/content/blogs-for-devs.yaml')))[$slug]
            ?? null;

        $html = $this->renderer->renderTemplate(
            $post['meta']['template'] ?? 'blog/meta.twig',
            ['post' => $post]
        );

        if (str_contains($request->getUri()->getQuery(), 'html') !== false) {
            return new Response(200, [], $html);
        }

        $path = File::path("public/img/meta/{$slug}.png");

        if (! is_dir(File::path('public/img/meta/'))) {
            mkdir(File::path('public/img/meta/'), 0777, true);
        }

        Browsershot::html($html)
            ->windowSize(1200, 627)
            ->save($path);

        return $this->response($path);
    }

    private function response(string $path): Response
    {
        return new Response(
            200,
            [
                'Content-Type' => 'image/png',
                'Content-Length' => filesize($path),
                'Cache-Control' => 'max-age=3600',
            ],
            file_get_contents($path)
        );
    }
}
