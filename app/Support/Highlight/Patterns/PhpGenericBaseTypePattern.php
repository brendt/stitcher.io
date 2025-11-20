<?php

namespace App\Support\Highlight\Patterns;

use Tempest\Highlight\IsPattern;
use Tempest\Highlight\Pattern;
use Tempest\Highlight\Tokens\TokenType;
use Tempest\Highlight\Tokens\TokenTypeEnum;

final readonly class PhpGenericBaseTypePattern implements Pattern
{
    use IsPattern;

    public function getPattern(): string
    {
        return '(?<match>\w+)\<';
    }

    public function getTokenType(): TokenType
    {
        return TokenTypeEnum::TYPE;
    }
}