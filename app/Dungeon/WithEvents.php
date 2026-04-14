<?php

namespace App\Dungeon;

interface WithEvents
{
    public function handle(Dungeon $dungeon, Tile $tile, object $event): void;
}