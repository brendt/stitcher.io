<?php

namespace App\Dungeon\Events;

use App\Dungeon\Card;
use App\Dungeon\Support\DungeonEvent;

final class PassiveCardUnset implements DungeonEvent
{
    public string $name = 'card.passsiveUnset';

    public array $payload = [];
}