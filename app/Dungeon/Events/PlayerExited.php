<?php

namespace App\Dungeon\Events;

use App\Dungeon\DungeonEvent;
use App\Support\Authentication\User;

final class PlayerExited implements DungeonEvent
{
    public string $name = 'player.exited';

    public array $payload = [];

    public function __construct(
        public readonly User $user,
        public readonly int $coins,
        public readonly int $victoryPoints,
        public readonly int $shards,
        public readonly int $experience,
    ) {}
}