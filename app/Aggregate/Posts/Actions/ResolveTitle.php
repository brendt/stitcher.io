<?php

namespace App\Aggregate\Posts\Actions;

use Tempest\HttpClient\HttpClient;
use Throwable;
use function Tempest\Support\str;

final readonly class ResolveTitle
{
    public function __construct(
        private HttpClient $http,
    ) {}

    public function __invoke(string $uri): string
    {
        try {
            $content = $this->http->get($uri, [
                'User-Agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1'
            ])->body ?? '';

            $content = str($content);

            $title = $content
                ->between('<title', '</title>')
                ->afterFirst('>')
                ->trim()
                ->replaceRegex("/(&#[0-9]+;)/", function ($match) {
                    return mb_convert_encoding($match[1], "UTF-8", "HTML-ENTITIES");
                })
                ->toString() ?: $uri;

            return html_entity_decode(html_entity_decode($title));
        } catch (Throwable) {
            return $uri;
        }
    }
}