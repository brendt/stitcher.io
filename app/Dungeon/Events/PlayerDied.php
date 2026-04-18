<?php

namespace App\Dungeon\Events;

use App\Dungeon\DungeonEvent;

final class PlayerDied implements DungeonEvent
{
    public string $name = 'player.died';

    public array $payload = [];
}