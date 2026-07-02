<?php

namespace App\Php\Support\Highlight;

use Tempest\Highlight\Language;
use Tempest\Highlight\Languages\Base\Injections\CustomClassInjection;

final readonly class RawLanguage implements Language
{
    public function getName(): string
    {
        return 'raw';
    }

    public function getAliases(): array
    {
        return [];
    }

    public function getInjections(): array
    {
        return [
            new CustomClassInjection(),
        ];
    }

    public function getPatterns(): array
    {
        return [];
    }
}
