<?php

namespace App\Aggregate\Posts;

use Tempest\Router\Prefix;
use Tempest\Router\Stateless;
use Tempest\Router\Get;
use App\Aggregate\Posts\Actions\QueuePost;
use App\Support\Authentication\Admin;
use Tempest\Database\Query;
use Tempest\DateTime\DateTime;
use Tempest\Http\Responses\Redirect;
use Tempest\Router;
use Tempest\View\View;
use Throwable;
use function Tempest\defer;
use function Tempest\View\view;

#[Prefix('/feed'), Stateless]
final class PostsController
{
    #[Get('/posts/{post}')]
    public function visit(Post $post): Redirect
    {
        defer(function () use ($post) {
            new Query('UPDATE posts SET visits = visits + 1 WHERE id = ?', [$post->id])->execute();

            try {
                new Query('UPDATE sources SET visits = visits + 1 WHERE id = ?', [$post->source->id])->execute();
            } catch (Throwable) {
                // This is a post without a source
            }
        });

        return new Redirect($post->uri);
    }

    #[Admin, Router\Post('/posts/deny/{post}')]
    public function deny(Post $post): View
    {
        $post->state = PostState::DENIED;
        $post->save();

        return $this->render();
    }

    #[Admin, Router\Post('/posts/publish/{post}')]
    public function publish(Post $post): View
    {
        $post->state = PostState::PUBLISHED;
        $post->publicationDate = DateTime::now();
        $post->save();

        return $this->render();
    }

    #[Admin, Router\Post('/posts/queue/{post}')]
    public function queue(Post $post, QueuePost $queuePost): View
    {
        $queuePost($post);

        return $this->render();
    }

    private function render(): View
    {
        $pendingPosts = Post::pending()->limit(5)->all();
        $shouldQueue = Post::shouldQueue();
        $futureQueued = Post::futureQueued();
        $pendingCount = Post::pendingCount();

        return \Tempest\View\view(
            'x-pending-posts.view.php',
            pendingPosts: $pendingPosts,
            shouldQueue: $shouldQueue,
            futureQueued: $futureQueued,
            pendingCount: $pendingCount,
        );
    }
}