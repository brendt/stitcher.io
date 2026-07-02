<?php

namespace App\Php\Support\Highlight;

use Tempest\Highlight\IsPattern;
use Tempest\Highlight\Pattern;
use Tempest\Highlight\Tokens\TokenType;
use Tempest\Highlight\Tokens\TokenTypeEnum;

final readonly class ShellKeywordPattern implements Pattern
{
    use IsPattern;

    public function getPattern(): string
    {
        return '/^(~ )?(?<match>.*?)\s/m';
    }

    public function getTokenType(): TokenType
    {
        return TokenTypeEnum::KEYWORD;
    }
}
