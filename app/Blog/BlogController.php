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
use Tempest\Router\Stateless;
use Tempest\Router\StaticPage;
use Tempest\View\View;
use Tempest\View\ViewRenderer;

use function Tempest\root_path;
use function Tempest\Router\uri;
use function Tempest\View\view;

final class BlogController
{
    #[Get('/')]
    #[StaticPage]
    public function index(BlogPostRepository $repository): View
    {
        $posts = $repository->all();

        return \Tempest\View\view('blog-overview.view.php', posts: $posts);
    }

    #[Get('/blog/{slug}')]
    #[StaticPage(BlogPostDataProvider::class)]
    public function show(
        string $slug,
        BlogPostRepository $repository,
        Authenticator $authenticator,
    ): Response|View {
        $post = $repository->find($slug);

        if (! $post) {
            return new NotFound();
        }

        return \Tempest\View\view(
            'blog-show.view.php',
            post: $post,
            comments: [],
            user: $authenticator->current(),
        );
    }

    #[Stateless]
    #[Get('/rss')]
    #[Get('/atom')]
    public function rss(
        ViewRenderer $viewRenderer,
        Cache $cache,
        BlogPostRepository $repository,
    ): Response {
        $xml = $cache->resolve(
            key: 'blog-rss',
            callback: fn () => $viewRenderer->render(\Tempest\View\view('blog-rss.view.php', posts: $repository->all())),
            expiration: DateTime::now()->plusHours(1),
        );

        return new Ok($xml)->addHeader('Content-Type', 'application/xml;charset=UTF-8');
    }
}
