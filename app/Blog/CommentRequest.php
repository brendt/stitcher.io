<?php

namespace App\Blog;

use Tempest\Http\IsRequest;
use Tempest\Http\Request;

final class CommentRequest implements Request
{
    use IsRequest;

    public string $comment;
}
