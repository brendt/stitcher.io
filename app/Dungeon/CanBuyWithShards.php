<?php

namespace App\Dungeon;

interface CanBuyWithShards
{
    public function getAdjustedPrice(): int;

    public function getShardPrice(): int;
}