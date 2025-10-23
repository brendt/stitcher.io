<?php

namespace App\PhpDocs\Elements;

use App\PhpDocs\Element;
use App\PhpDocs\HasChildren;

final class VoidElement implements Element
{
    public function render(): string
    {
        return '';
    }
}