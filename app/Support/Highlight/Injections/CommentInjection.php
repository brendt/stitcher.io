<?php

declare(strict_types=1);

namespace App\Support\Highlight\Injections;

use App\Support\Highlight\IsTagInjection;
use Tempest\Highlight\Escape;
use Tempest\Highlight\Injection;

final readonly class CommentInjection implements Injection
{
    use IsTagInjection;

    public function getTag(): string
    {
        return 'comment';
    }

    public function style(string $content): string
    {
        $lines = explode(PHP_EOL, $content);

        if (count($lines) > 1) {
            $comment = implode(
                PHP_EOL,
                [
                    '/*',
                    ...array_map(
                        fn (string $line) => " * {$line}",
                        $lines,
                    ),
                    ' */',
                ],
            );
        } else {
            $comment = '// ' . $lines[0];
        }

        return sprintf(
            '%s%s%s',
            Escape::tokens('<span class="hl-console-comment">'),
            $comment,
            Escape::tokens('</span>'),
        );
    }
}
