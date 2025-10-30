<?php

namespace App\Map\Item;

interface HandHeldItem extends Item
{
    public function getModifier(): int;
}
