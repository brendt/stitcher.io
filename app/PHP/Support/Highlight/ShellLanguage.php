<?php

namespace App\PHP\Support\Highlight;

use Tempest\Highlight\Language;

final readonly class ShellLanguage implements Language
{
    public function getName(): string
    {
        return 'shell';
    }

    public function getAliases(): array
    {
        return ['sh'];
    }

    public function getInjections(): array
    {
        return [];
    }

    public function getPatterns(): array
    {
        return [
            new ShellCommentPattern(),
            new ShellStartPattern(),
            new ShellParameterPattern(),
            new ShellKeywordPattern(),
        ];
    }
}