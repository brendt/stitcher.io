<?php

namespace App\Blog;

use App\Authentication\AuthMiddleware;
use Tempest\Auth\Authentication\Authenticator;
use Tempest\DateTime\DateTime;
use Tempest\Router\Get;
use Tempest\Router\Post;
use Tempest\View\View;

final readonly class CommentsController
{
    public function __construct(
        private BlogPostRepository $repository,
        private Authenticator $authenticator,
    ) {}

    #[Get('/blog/{slug}/comments')]
    public function index(string $slug): View
    {
        $post = $this->repository->find($slug);

        return $this->render($post);
    }

    #[Post('/blog/{slug}/comments', middleware: [AuthMiddleware::class])]
    public function comment(string $slug, CommentRequest $request): View
    {
        $post = $this->repository->find($slug);

        if (strlen($request->comment) < 5) {
            return $this->render(
                $post,
                commentError: 'Your comment must be at least 5 characters long.',
            );
        }

        Comment::create(
            user: $this->authenticator->current(),
            for: $post->slug,
            content: $request->comment,
            createdAt: DateTime::now(),
        );

        return $this->render($post);
    }

    private function render(BlogPost $post, mixed ...$data): View
    {
        $comments = Comment::select()
            ->with('user')
            ->where('for', $post->slug)
            ->orderBy('createdAt DESC')
            ->all();

        return \Tempest\view(
            __DIR__ . '/../ViewComponents/x-comments.view.php',
            ...$data,
            post: $post,
            user: $this->authenticator->current(),
            comments: $comments
        );
    }
}