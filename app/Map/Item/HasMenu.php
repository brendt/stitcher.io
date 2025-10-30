<?php

namespace App\Map\Item;

use App\Map\Menu;

interface HasMenu
{
    public function getMenu(): ?Menu;

    public function saveMenu(array $form): void;
}
