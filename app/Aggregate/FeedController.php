<?php

namespace App\Aggregate;

use App\Support\Authentication\User;
use App\Aggregate\Posts\Post;
use App\Aggregate\Suggestions\Suggestion;
use Closure;
use Tempest\Auth\Authentication\Authenticator;
use Tempest\Cache\Cache;
use Tempest\DateTime\DateTime;
use Tempest\DateTime\FormatPattern;
use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\Http\Responses\Ok;
use Tempest\Router\Get;
use Tempest\Router\Prefix;
use Tempest\Router\Stateless;
use Tempest\Support\Arr\ImmutableArray;
use Tempest\View\View;
use Tempest\View\ViewRenderer;
use function Tempest\Support\arr;
use function Tempest\View\view;

#[Prefix('/feed')]
final class FeedController
{
    #[Get('/')]
    public function home(Authenticator $authenticator, Request $request): View
    {
        $posts = arr(Post::published()
            ->orderBy('publicationDate DESC')
            ->limit(20)
            ->all());

        /** @var User $user */
        $user = $authenticator->current();

        if ($user?->isAdmin) {
            $futureQueued = Post::futureQueued();
            $pendingPosts = Post::pending()->limit(5)->all();
            $shouldQueue = Post::shouldQueue();
            $pendingCount = Post::pendingCount();
            $suggestions = Suggestion::select()->all();
        }

        return \Tempest\View\view(
            'feed.view.php',
            user: $user,
            posts: $posts,
            color: $this->createColorFunction($posts),
            pendingPosts: $pendingPosts ?? [],
            shouldQueue: $shouldQueue ?? null,
            futureQueued: $futureQueued ?? null,
            pendingCount: $pendingCount ?? null,
            suggestions: $suggestions ?? [],
            success: $request->has('success'),
        );
    }

    #[Get('/top')]
    public function top(Authenticator $authenticator): View
    {
        $posts = arr(Post::published()
            ->orderBy('posts.visits DESC')
            ->where('publicationDate > ?', DateTime::now()->minusDays(31)->startOfDay()->format(FormatPattern::SQL_DATE_TIME))
            ->limit(20)
            ->all());

        $posts = $posts->sortByCallback(fn (Post $a, Post $b) => $b->publicationDate <=> $a->publicationDate);

        return \Tempest\View\view(
            'feed.view.php',
            posts: $posts,
            color: $this->createColorFunction($posts),
            pendingPosts: $pendingPosts ?? [],
            shouldQueue: $shouldQueue ?? null,
            futureQueued: $futureQueued ?? null,
            pendingCount: $pendingCount ?? null,
            suggestions: $suggestions ?? [],
            user: $authenticator->current(),
            page: null,
        );
    }

    #[Stateless, Get('/rss')]
    public function __invoke(
        ViewRenderer $viewRenderer,
        Cache $cache,
    ): Response
    {
        $xml = $cache->resolve(
            key: 'feed-rss',
            callback: fn () => $viewRenderer->render(\Tempest\View\view(
                __DIR__ . '/feed-rss.view.php',
                posts: Post::published()
                    ->orderBy('publicationDate DESC')
                    ->limit(50)
                    ->all(),
            )),
            expiration: DateTime::now()->plusHours(1),
        );

        return new Ok($xml)->addHeader('Content-Type', 'application/xml;charset=UTF-8');
    }

    private function createColorFunction(ImmutableArray $posts): Closure
    {
        $postRating = $posts
            ->sortByCallback(fn (Post $a, Post $b) => $b->visits <=> $a->visits)
            ->values()
            ->mapWithKeys(fn (Post $post, int $index) => yield $post->id->value => $index);

        return fn (Post $post) => $this->color($postRating[$post->id->value] ?? null);
    }

    public function color(?int $index): string
    {
        return match (true) {
            $index === 0 => 'bg-slate-400',
            $index < 4 => 'bg-slate-300',
            $index < 6 => 'bg-slate-200',
            $index <= 10 => 'bg-slate-100',
            default => 'bg-gray-100',
        };
    }
}
