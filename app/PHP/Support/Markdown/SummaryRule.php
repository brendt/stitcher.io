<?php

namespace App\PHP\Support\Markdown;

use Tempest\Markdown\Parser;
use Tempest\Markdown\ProvidesFirstChar;
use Tempest\Markdown\Rule;
use Tempest\Markdown\Token;

final class SummaryRule implements Rule, ProvidesFirstChar
{
    public string $firstChar = '{';

    public function shouldParse(Parser $parser): bool
    {
        return $parser->comesNext('{{{', 3);
    }

    public function parse(Parser $parser): Token
    {
        $parser->consumeIncluding('{{{');

        $summary = $parser->consumeUntil(Parser::NEW_LINE);

        $parser->consumeWhile(Parser::NEW_LINE);

        $content = $parser->consumeUntilString('}}}');

        $parser->consumeIncluding('}}}');
        $parser->consumeWhile(Parser::NEW_LINE);

        // Remove trailing newline.
        if (str_ends_with($content, PHP_EOL)) {
            $content = substr($content, 0, -1);
        }

        return new SummaryToken(
            summary: $summary,
            content: $content,
        );
    }
}
