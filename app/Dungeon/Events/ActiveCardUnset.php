<?php

namespace App\Dungeon\Events;

use App\Dungeon\Card;
use App\Dungeon\Support\DungeonEvent;

final class ActiveCardUnset implements DungeonEvent
{
    public string $name = 'card.activeUnset';

    public array $payload = [];
}