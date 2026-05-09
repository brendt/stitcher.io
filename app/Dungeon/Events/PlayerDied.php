<?php

namespace App\Dungeon\Events;

use App\Dungeon\DungeonEvent;
use App\Support\Authentication\User;

final class PlayerDied implements DungeonEvent
{
    public string $name = 'player.died';

    public array $payload = [];

    public function __construct(
        public readonly User $user,
    ) {}
}