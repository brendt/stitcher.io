<?php

namespace App\Dungeon\Events;

use App\Support\Authentication\User;

final class UserShopInitialized
{
    public function __construct(
        public User $user,
    ) {}
}