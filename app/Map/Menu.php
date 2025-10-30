<?php

namespace App\Map;

use App\Map\Item\HasMenu;
use Illuminate\View\View;

final class Menu
{
    public function __construct(
        public HasMenu $hasMenu,
        public string $viewPath,
        public array $form = [],
    ) {}

    public function render(): View
    {
        return view($this->viewPath);
    }
}
