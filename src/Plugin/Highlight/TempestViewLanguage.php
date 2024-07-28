<?php

declare(strict_types=1);

namespace Brendt\Stitcher\Plugin\Highlight;

use Brendt\Stitcher\Plugin\Highlight\Injections\TempestViewEchoInjection;
use Brendt\Stitcher\Plugin\Highlight\Injections\TempestViewPhpInjection;
use Brendt\Stitcher\Plugin\Highlight\Patterns\TempestViewDynamicAttributePattern;
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
