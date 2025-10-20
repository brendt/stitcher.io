<?php

namespace App\Blog;

use Tempest\Auth\Authentication\Authenticator;
use Tempest\Cache\Cache;
use Tempest\DateTime\DateTime;
use Tempest\Http\Response;
use Tempest\Http\Responses\Ok;
use Tempest\Router\Get;
use Tempest\Router\StaticPage;
use Tempest\View\View;
use Tempest\View\ViewRenderer;
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
    public function show(string $slug, BlogPostRepository $repository, Authenticator $authenticator): View
    {
        $post = $repository->find($slug);

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

    #[Get('/rss')]
    #[Get('/feed')]
    #[Get('/atom')]
    public function rss(ViewRenderer $viewRenderer, Cache $cache, BlogPostRepository $repository): Response
    {
        $xml = $cache->resolve(
            key: 'rss',
            callback: fn () => $viewRenderer->render(view('blog-rss.view.php', posts: $repository->all())),
            expiration: DateTime::now()->plusHours(1),
        );

        return new Ok($xml)
            ->addHeader('Content-Type', 'application/xml;charset=UTF-8');
    }
}