<?php

namespace App\Dungeon;

interface CheckBeforePlaying
{
    public function canPlay(Dungeon $dungeon): bool;
}
