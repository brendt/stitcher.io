<?php

declare(strict_types=1);

namespace Brendt\Stitcher\Plugin\Highlight\Injections;

use Brendt\Stitcher\Plugin\Highlight\IsTagInjection;
use Tempest\Highlight\Escape;
use Tempest\Highlight\Injection;

final readonly class SuccessInjection implements Injection
{
    use IsTagInjection;

    public function getTag(): string
    {
        return 'success';
    }

    public function style(string $content): string
    {
        return sprintf(
            '%s%s%s',
            Escape::tokens('<span class="hl-console-success">'),
            $content,
            Escape::tokens('</span>'),
        );
    }
}
