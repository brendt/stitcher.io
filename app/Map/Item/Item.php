<?php

namespace App\Map\Item;

use App\Map\MapGame;
use App\Map\Tile\Tile;

interface Item
{
    public function getId(): string;

    public function getName(): string;

    public function getPrice(): ItemPrice;

    public function canInteract(MapGame $game, Tile $tile): bool;
}
