<?php

namespace App\Dungeon;

interface PassiveCard
{
    public ?string $label { get; }

    public function handle(Dungeon $dungeon, DungeonEvent $event): void;
}