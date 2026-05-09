<?php

namespace App\Support\Authentication;

use App\Blog\Comment;
use Tempest\Auth\Authentication\Authenticatable;
use Tempest\Database\IsDatabaseModel;
use Tempest\Database\Virtual;

final class User implements Authenticatable
{
    use IsDatabaseModel;

    public function __construct(
        public string $name,
        public string $email,
        public Role $role = Role::USER,
    ) {}

    #[Virtual]
    public bool $isAdmin {
        get => $this->role === Role::ADMIN;
    }

    public function owns(Comment $comment): bool
    {
        return $this->isAdmin || $comment->user->id->equals($this->id);
    }
}
