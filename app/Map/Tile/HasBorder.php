<?php

namespace App\Map\Tile;

interface HasBorder
{
    public function getBorderColor(): string;
}
