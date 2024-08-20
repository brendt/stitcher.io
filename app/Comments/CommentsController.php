<?php

namespace App\Comments;

use App\Auth\Authenticator;
use Tempest\Http\Get;
use Tempest\View\View;
use function Tempest\view;

final class CommentsController
{
    public function __construct(private Authenticator $authenticator) {}

    #[Get('/app/comments/{postId}')]
    public function get(string $postId): View
    {
        $user = $this->authenticator->getLoggedInUser();

        $comments = Comment::query()
            ->with('user')
            ->where('postId = ?', $postId)
            ->orderBy('createdAt DESC')
            ->all();

        return view(__DIR__ . '/comments.view.php')
            ->data(
                user: $user,
                comments: $comments,
                back: "/blog/{$postId}",
            );
    }
}