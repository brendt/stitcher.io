<?php

namespace App\Dungeon\Events;

use App\Dungeon\Support\DungeonEvent;

final class PlayerExited implements DungeonEvent
{
    public string $name = 'player.exited';

    public array $payload = [];
}