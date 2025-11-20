<?php

namespace App\Support\Highlight;

use App\Support\Highlight\Patterns\PhpGenericBaseTypePattern;
use App\Support\Highlight\Patterns\PhpGenericTypePattern;
use Tempest\Highlight\Languages\Php\PhpLanguage;

final class ExtendedPhpLanguage extends PhpLanguage
{
    public function getPatterns(): array
    {
        return [
            ...parent::getPatterns(),
            new PhpGenericTypePattern(),
            new PhpGenericBaseTypePattern(),
        ];
    }
}