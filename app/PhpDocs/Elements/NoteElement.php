<?php

namespace App\PhpDocs\Elements;

use App\PhpDocs\Element;
use App\PhpDocs\HasChildren;

final class NoteElement implements Element, HasChildren
{
    public array $children = [];

    public function __construct(
        private readonly string $content,
    ) {}

    public function render(): string
    {
        return sprintf(<<<'TXT'
            <div class="note">
                %s
            </div>
            TXT,
            implode(PHP_EOL, array_map(fn (Element $element) => $element->render(), $this->children)),
        );
    }
}