<?php

namespace App\Blog;

use App\Support\Authentication\AuthMiddleware;
use Tempest\Auth\Authentication\Authenticator;
use Tempest\DateTime\DateTime;
use Tempest\Http\Session\Session;
use Tempest\Router\Get;
use Tempest\Router\Post;
use Tempest\View\View;
use function Tempest\Support\str;

final readonly class CommentsController
{
    public function __construct(
        private BlogPostRepository $repository,
        private Authenticator $authenticator,
        private Session $session,
    ) {}

    #[Get('/blog/{slug}/comments')]
    public function index(string $slug): View
    {
        sleep(1);
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
                commentError: 'Your comment must be more than 5 characters long',
                currentComment: $request->comment,
            );
        }

        Comment::create(
            user: $this->authenticator->current(),
            for: $post->slug,
            content: str($request->comment)->stripTags()->toString(),
            createdAt: DateTime::now(),
        );

        return $this->render($post);
    }

    #[Post('/blog/{slug}/comments/{id}/delete', middleware: [AuthMiddleware::class])]
    public function delete(string $slug, int $id): View
    {
        $post = $this->repository->find($slug);

        $comment = Comment::select()
            ->where('id', $id)
            ->where('for', $slug)
            ->where('user_id = ?', $this->authenticator->current()->id->value)
            ->first();

        if (! $comment) {
            return $this->render($post);
        }

        $deleting = $this->session->get('comment_deleting');

        if ($deleting === null || $deleting !== $id) {
            $this->session->set('comment_deleting', $comment->id->value);

            return $this->render($post, deleting: $id);
        }

        $comment->delete();
        $this->session->remove('comment_deleting');

        return $this->render($post);
    }

    private function render(BlogPost $post, mixed ...$data): View
    {
        $comments = Comment::select()
            ->with('user')
            ->where('for', $post->slug)
            ->orderBy('createdAt DESC')
            ->all();

        return \Tempest\View\view(
            __DIR__ . '/../ViewComponents/x-comments.view.php',
            ...$data,
            post: $post,
            user: $this->authenticator->current(),
            comments: $comments
        );
    }
}