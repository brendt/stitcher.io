<?php

namespace App\Support\Markdown;

use Tempest\Markdown\Lexer;
use Tempest\Markdown\Rule;
use Tempest\Markdown\Token;

final readonly class SnippetRule implements Rule
{
    public function shouldLex(Lexer $lexer): bool
    {
        return $lexer->comesNext('{{', 2);
    }

    public function lex(Lexer $lexer): ?Token
    {
        $lexer->consumeWhile('{');

        $snippet = $lexer->consumeUntil('}');

        $lexer->consumeWhile('}');

        return new SnippetToken($snippet);
    }
}