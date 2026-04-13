<?php

namespace App\Dungeon\Support;

interface DungeonEvent
{
    public string $name {
        get;
    }

    public array $payload {
        get;
    }
}