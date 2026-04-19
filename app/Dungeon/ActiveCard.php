<?php

namespace App\Dungeon;

interface ActiveCard
{
    public ?string $label { get; }

    public function canInteractWithTile(Dungeon $dungeon, Tile $tile): bool;

    public function interactWithTile(Dungeon $dungeon, Tile $tile): void;
}
