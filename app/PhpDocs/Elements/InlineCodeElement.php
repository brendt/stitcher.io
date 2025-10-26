<?php

namespace App\PhpDocs\Elements;

use App\PhpDocs\Element;
use Tempest\Highlight\Highlighter;

final readonly class InlineCodeElement implements Element
{
    public function __construct(
        private string $code,
        private Highlighter $highlighter,
    ) {}

    public function render(): string
    {
        $parsedCode = $this->highlighter->parse(trim($this->code), 'php');

        return "<code>{$parsedCode}</code>";
    }
}