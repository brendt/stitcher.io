<?php

namespace App\PhpDocs\Elements;

use App\PhpDocs\Element;

final class VoidElement implements Element
{
    public function render(): string
    {
        return '';
    }
}