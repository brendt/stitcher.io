<?php

namespace App\Blog;

use App\Authentication\User;
use Tempest\Database\IsDatabaseModel;
use Tempest\DateTime\DateTime;

final class Comment
{
    use IsDatabaseModel;

    public function __construct(
        public User $user,
        public string $for,
        public string $content,
        public DateTime $createdAt,
    ) {}
}