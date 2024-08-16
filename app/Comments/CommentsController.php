<?php

namespace App\Comments;

use Tempest\Http\Get;
use Tempest\View\View;
use function Tempest\view;

final class CommentsController
{
    #[Get('/app/comments/{postId}')]
    public function get(string $postId): View
    {
        $comments = Comment::query()
            ->where('postId = :postId', postId: $postId)
            ->all();

        return view(__DIR__ . '/comments.view.php')
            ->data(
                comments: $comments,
            );
    }
}