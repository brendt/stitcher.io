<?php

namespace App\Comments;

use App\Auth\User;
use DateTimeImmutable;
use Tempest\Database\DatabaseModel;
use Tempest\Database\IsDatabaseModel;

final class Comment implements DatabaseModel
{
    use IsDatabaseModel;

    public function __construct(
        public User $user,
        public string $postId,
        public string $comment,
        public DateTimeImmutable $createdAt,
    ) {}
}