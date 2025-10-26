<?php

namespace App\PhpDocs\Elements;

use App\PhpDocs\Element;
use App\PhpDocs\HasChildren;

final class DefaultElement implements Element, HasChildren
{
    public array $children = [];

    public function __construct(
        private readonly string $name,
        private readonly ?string $textContent,
    ) {}

    public function render(): string
    {

        return sprintf(<<<'TXT'
<div class="php-unparsed">
UNPARSED: %s

%s
</div>
TXT,
            $this->name,
            $this->textContent ?? '',
        );
    }
}