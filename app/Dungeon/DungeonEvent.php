<?php

namespace App\Dungeon;

interface DungeonEvent
{
    public string $name {
        get;
    }

    public array $payload {
        get;
    }
}