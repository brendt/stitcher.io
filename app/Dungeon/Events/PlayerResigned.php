<?php

namespace App\Dungeon\Events;

use App\Dungeon\DungeonEvent;
use App\Support\Authentication\User;

final class PlayerResigned implements DungeonEvent
{
    public string $name = 'player.resigned';

    public array $payload = [];

    public function __construct(
        public User $user
    ) {}
}