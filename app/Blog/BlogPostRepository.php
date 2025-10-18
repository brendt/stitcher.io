<?php

namespace App\Blog;

use League\CommonMark\MarkdownConverter;
use Spatie\YamlFrontMatter\YamlFrontMatter;
use Tempest\Cache\Cache;
use Tempest\DateTime\DateTime;
use Tempest\Support\Arr\ImmutableArray;
use function Tempest\Support\arr;

final readonly class BlogPostRepository
{
    public function __construct(
        private MarkdownConverter $converter,
        private Cache $cache,
    ) {}

    public function find(string $slug): ?BlogPost
    {
        return $this->all()->first(fn (BlogPost $post) => $post->slug === $slug);
    }

    /**
     * @return ImmutableArray<array-key, \App\Blog\BlogPost>
     */
    public function all(): ImmutableArray
    {
        $posts = arr(glob(__DIR__ . "/Content/*.md"))
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
            ->mapTo(BlogPost::class)
            ->sortByCallback(fn (BlogPost $a, BlogPost $b) => $b->date <=> $a->date);

        foreach ($posts as $i => $post) {
            $next = $posts[$i + 1] ?? null;

            if (! $next) {
                continue;
            }

            $post->next = $next;
        }

        return $posts;
    }

    private function parseDate(string $path): DateTime
    {
        preg_match('#\d+-\d+-\d+#', $path, $matches);

        $date = $matches[0] ?? null;

        return DateTime::parse($date ?? 'now');
    }
}