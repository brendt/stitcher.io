<?php

namespace App\Php\Docs\Parser\Elements;

use App\Php\Docs\Parser\Element;
use Tempest\Highlight\Highlighter;

final readonly class CodeElement implements Element
{
    public function __construct(
        private string $code,
        private ?string $language,
        private Highlighter $highlighter,
    ) {}

    public function render(): string
    {
        $parsedCode = $this->highlighter->parse(trim($this->code), $this->language ?? 'php');

        return sprintf(<<<'TXT'
            <pre>%s</pre>
            TXT,
            $parsedCode,
        );
    }
}