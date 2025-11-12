<?php

namespace App\Blog;

use HeadlessChromium\BrowserFactory;
use Tempest\Auth\Authentication\Authenticator;
use Tempest\Cache\Cache;
use Tempest\DateTime\DateTime;
use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\Http\Responses\File;
use Tempest\Http\Responses\NotFound;
use Tempest\Http\Responses\Ok;
use Tempest\Router\Get;
use Tempest\Router\SetCurrentUrlMiddleware;
use Tempest\Router\Stateless;
use Tempest\Router\StaticPage;
use Tempest\View\View;
use Tempest\View\ViewRenderer;
use function Tempest\root_path;
use function Tempest\Router\uri;
use function Tempest\view;

final class BlogController
{
    #[Get('/')]
    #[StaticPage]
    public function index(BlogPostRepository $repository): View
    {
        $posts = $repository->all();

        return \Tempest\view('blog-overview.view.php', posts: $posts);
    }

    #[Get('/blog/{slug}')]
    #[StaticPage(BlogPostDataProvider::class)]
    public function show(
        string $slug,
        BlogPostRepository $repository,
        Authenticator $authenticator,
    ): Response|View
    {
        $post = $repository->find($slug);

        if (! $post) {
            return new NotFound();
        }

        $comments = Comment::select()
            ->with('user')
            ->where('for', $post->slug)
            ->orderBy('createdAt DESC')
            ->all();

        return \Tempest\view(
            'blog-show.view.php',
            post: $post,
            comments: $comments,
            user: $authenticator->current(),
        );
    }

    #[Stateless]
    #[Get('/rss')]
    #[Get('/feed')]
    #[Get('/atom')]
    public function rss(
        ViewRenderer $viewRenderer,
        Cache $cache,
        BlogPostRepository $repository,
    ): Response
    {
        $xml = $cache->resolve(
            key: 'rss',
            callback: fn () => $viewRenderer->render(view('blog-rss.view.php', posts: $repository->all())),
            expiration: DateTime::now()->plusHours(1),
        );

        return new Ok($xml)->addHeader('Content-Type', 'application/xml;charset=UTF-8');
    }

    #[Stateless, Get('/blog/{slug}/meta.png')]
    public function metaPng(
        string $slug,
        Request $request,
        BlogPostRepository $repository,
        ViewRenderer $viewRenderer,
    ): Response
    {
        return $this->meta($slug, $request, $repository, $viewRenderer);
    }

    #[Stateless, Get('/blog/{slug}/meta')]
    public function meta(
        string $slug,
        Request $request,
        BlogPostRepository $repository,
        ViewRenderer $viewRenderer,
    ): Response
    {
        $post = $repository->find($slug);

        if (! $post) {
            return new NotFound();
        }

        if ($request->has('html')) {
            $html = $viewRenderer->render(view('blog-meta.view.php', post: $post));

            return new Ok($html);
        }

        $path = root_path('public/blog/' . $slug . '/meta.png');

        if (is_file($path) && ! $request->has('nocache')) {
            return new File($path);
        }

        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), recursive: true);
        }

        $browser = new BrowserFactory()->createBrowser([
            'windowSize' => [1200, 628],
        ]);

        try {
            $page = $browser->createPage();

            $page->setDeviceMetricsOverride([
                'width' => 1200,
                'height' => 628,
            ]);

            $page->navigate(uri([self::class, 'meta'], slug: $slug, html: true))->waitForNavigation();

            $screenshot = $page->screenshot([
                'captureBeyondViewport' => true,
                'clip' => $page->getFullPageClip(),
            ]);

            $screenshot->saveToFile($path);
        } finally {
            $browser->close();
        }

        return new File($path);
    }
}