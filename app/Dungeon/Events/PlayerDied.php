<?php

namespace App\Dungeon\Events;

use App\Dungeon\Support\DungeonEvent;

final class PlayerDied implements DungeonEvent
{
    public string $name = 'player.died';

    public array $payload = [];
}