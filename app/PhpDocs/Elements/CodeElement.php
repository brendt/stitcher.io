<?php

namespace App\PhpDocs\Elements;

use App\PhpDocs\Element;

final readonly class CodeElement implements Element
{
    public function __construct(
        private string $code,
        private ?string $language,
    ) {}

    public function render(): string
    {
        return sprintf(<<<'TXT'
        
        ```%s
        %s
        ```
        TXT,
        $this->language ?? '',
        trim($this->code));
    }
}