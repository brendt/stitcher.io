<?php

namespace App\PHP\Support\Markdown;

use Tempest\Markdown\Parser;
use Tempest\Markdown\Token;

final readonly class PreToken implements Token
{
    public function __construct(
        public ?string $language,
        public string $content,
    ) {}

    public function parse(Parser $parser): string
    {
        $language = $this->language;

        if (! $language && $parser->highlighter) {
            $language = $parser->highlighter->fallbackLanguage?->getName();
        }

        $content = $this->content;

        $titleDelimiter = match ($language) {
            'php' => '// ',
            default => null,
        };

        if ($titleDelimiter && str_starts_with($content, '// ')) {
            [$title, $content] = explode(PHP_EOL, $content, 2);
            $title = ltrim($title, '/ ');
            $content = ltrim($content, PHP_EOL);
        } else {
            $title = $language;
        }

        if ($parser->highlighter) {
            $content = $parser->highlighter->parse($content, $language);
        }

        $class = $language ? " class=\"language-{$language}\"" : '';

        $html = "<div class=\"codeblock\"><span>{$title}</span><pre{$class}>{$content}</pre></div>";

        return $html;
    }
}