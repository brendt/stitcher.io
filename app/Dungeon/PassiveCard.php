<?php

namespace App\Dungeon;

interface PassiveCard
{
    public ?string $label { get; }

    public function handle(Dungeon $dungeon, Tile $tile, object $event): void;
}