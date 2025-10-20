<?php

namespace App\Blog;

use Tempest\Http\IsRequest;
use Tempest\Http\Request;
use Tempest\Validation\Rules\HasLength;

final class CommentRequest implements Request
{
    use IsRequest;

    public string $comment;
}