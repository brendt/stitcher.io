<?php

namespace App\PHP\Support\Markdown;

use Tempest\Markdown\Parser;
use Tempest\Markdown\Token;

final readonly class SummaryToken implements Token
{
    public function __construct(
        public string $summary,
        public string $content,
    ) {}

    public function parse(Parser $parser): string
    {
        $content = $parser->parse($this->content);

        return sprintf(
            '<details><summary>%s</summary>%s</details>',
            $this->summary,
            $content,
        );
    }
}
