<?php

namespace App\Support\Markdown;

use Tempest\Markdown\Parser;
use Tempest\Markdown\Rule;
use Tempest\Markdown\Token;

final readonly class SnippetRule implements Rule
{
    public function shouldParse(Parser $parser): bool
    {
        return $parser->comesNext('{{', 2);
    }

    public function parse(Parser $parser): ?Token
    {
        $parser->consumeWhile('{');

        $snippet = $parser->consumeUntil('}');

        $parser->consumeWhile('}');

        return new SnippetToken($snippet);
    }
}
