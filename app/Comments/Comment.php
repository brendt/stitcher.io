<?php

namespace App\Comments;

use DateTimeImmutable;
use Tempest\Database\IsModel;
use Tempest\Database\Model;

final class Comment implements Model
{
    use IsModel;

    public function __construct(
        public string $postId,
        public string $email,
        public string $comment,
        public DateTimeImmutable $createdAt,
        public string $ip,
    ) {}
}