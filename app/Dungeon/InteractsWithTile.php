<?php

namespace App\Dungeon;

interface InteractsWithTile
{
    public function canInteractWithTile(Dungeon $dungeon, Tile $tile): bool;

    public function interactWithTile(Dungeon $dungeon, Tile $tile): void;
}
