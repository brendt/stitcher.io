<?php

namespace App\Mail;

use League\CommonMark\MarkdownConverter;
use Spatie\YamlFrontMatter\YamlFrontMatter;
use Tempest\Cache\Cache;
use Tempest\DateTime\DateTime;
use Tempest\Support\Arr\ImmutableArray;
use function Tempest\Support\arr;

final readonly class MailRepository
{
    public function __construct(
        private MarkdownConverter $converter,
        private Cache $cache,
    ) {
    }

    public function find(string $slug): ?Mail
    {
        $path = glob(__DIR__ . "/Content/*{$slug}.md")[0];
        $content = file_get_contents($path);
        $cacheKey = crc32($content);

        $this->cache->remove($cacheKey);

        return $this->all()->first(fn (Mail $mail) => $mail->slug === $slug);
    }

    /**
     * @return ImmutableArray<array-key, Mail>
     */
    public function all(): ImmutableArray
    {
        return arr(glob(__DIR__ . "/Content/*.md"))
            ->map(function (string $path) {
                $content = file_get_contents($path);
                $cacheKey = crc32($content);

                return $this->cache->resolve($cacheKey, function () use ($path, $content){
                    preg_match('/\d+-\d+-\d+-(?<slug>.*)\.md/', $path, $matches);

                    return [
                        'slug' => $matches['slug'],
                        'date' => $this->parseDate($path),
                        'content' => $this->converter->convert($content)->getContent(),
                        ...YamlFrontMatter::parse($content)->matter(),
                    ];
                });
            })
            ->mapTo(Mail::class)
            ->sortByCallback(fn (Mail $a, Mail $b) => $b->date <=> $a->date);
    }

    private function parseDate(string $path): DateTime
    {
        preg_match('#\d+-\d+-\d+#', $path, $matches);

        $date = $matches[0] ?? null;

        return DateTime::parse($date ?? 'now');
    }
}