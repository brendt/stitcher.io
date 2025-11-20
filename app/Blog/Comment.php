<?php

namespace App\Blog;

use App\Support\Authentication\User;
use Tempest\Database\IsDatabaseModel;
use Tempest\DateTime\DateTime;
use function Tempest\Router\uri;

final class Comment
{
    use IsDatabaseModel;

    public function __construct(
        public User $user,
        public string $for,
        public string $content,
        public DateTime $createdAt,
    ) {}

    public string $anchor {
        get => 'comment-' . $this->id;
    }

    public string $uri {
        get => uri([BlogController::class, 'show'], slug: $this->for) . '#' . $this->anchor;
    }
}