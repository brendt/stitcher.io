<?php

declare(strict_types=1);

namespace Brendt\Stitcher\Plugin\Highlight\Injections;

use Brendt\Stitcher\Plugin\Highlight\IsTagInjection;
use Tempest\Highlight\Escape;
use Tempest\Highlight\Injection;

final readonly class H2Injection implements Injection
{
    use IsTagInjection;

    public function getTag(): string
    {
        return 'h2';
    }

    public function style(string $content): string
    {
        return sprintf(
            '%s%s%s',
            Escape::tokens('<span class="hl-console-h2">'),
            $content,
            Escape::tokens('</span>'),
        );
    }
}
