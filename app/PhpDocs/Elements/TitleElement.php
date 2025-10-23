<?php

namespace App\PhpDocs\Elements;

use App\PhpDocs\Element;
use App\PhpDocs\HasChildren;

final class TitleElement implements Element
{
    public function __construct(
        private readonly string $title,
    ) {}

    public function render(): string
    {
        return "## {$this->title}";
    }
}