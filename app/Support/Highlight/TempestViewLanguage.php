<?php

declare(strict_types=1);

namespace App\Support\Highlight;

use App\Support\Highlight\Injections\TempestViewEchoInjection;
use App\Support\Highlight\Injections\TempestViewPhpInjection;
use App\Support\Highlight\Patterns\TempestViewDynamicAttributePattern;
use Tempest\Highlight\Languages\Html\HtmlLanguage;

final class TempestViewLanguage extends HtmlLanguage
{
    public function getName(): string
    {
        return 'html';
    }

    public function getAliases(): array
    {
        return [];
    }

    public function getInjections(): array
    {
        return [
            ...parent::getInjections(),
            new TempestViewPhpInjection(),
            new TempestViewEchoInjection(),
        ];
    }

    public function getPatterns(): array
    {
        return [
            ...parent::getPatterns(),
            new TempestViewDynamicAttributePattern(),
        ];
    }
}
