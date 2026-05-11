<?php

namespace App\Php\Docs\Parser\Elements;

use App\Php\Docs\Parser\Element;

final class VoidElement implements Element
{
    public function render(): string
    {
        return '';
    }
}