<?php

namespace App\Blog;

use App\Blog\Events\AllBlogPostsRetrieved;
use League\CommonMark\MarkdownConverter;
use Spatie\YamlFrontMatter\YamlFrontMatter;
use Tempest\Cache\Cache;
use Tempest\DateTime\DateTime;
use Tempest\Support\Arr\ImmutableArray;
use function Tempest\event;
use function Tempest\Router\uri;
use function Tempest\Support\arr;

final readonly class BlogPostRepository
{
    public function __construct(
        private MarkdownConverter $converter,
        private Cache $cache,
    ) {}

    public function find(string $slug): ?BlogPost
    {
        $path = glob(__DIR__ . "/Content/*{$slug}.md")[0] ?? null;

        if (! $path) {
            return null;
        }

        $content = file_get_contents($path);

        $frontMatter = YamlFrontMatter::parse($content)->matter();

        $meta = [
            'image' => uri([BlogController::class, 'metaPng'], slug: $slug),
            ...($frontMatter['meta'] ?? []),
        ];

        unset($frontMatter['meta'], $frontMatter['next']);

        $data = [
            'slug' => $slug,
            'date' => $this->parseDate($path),
            'content' => $this->converter->convert($content)->getContent(),
            'meta' => $meta,
            ...$frontMatter,
        ];

        return \Tempest\map($data)->to(BlogPost::class);
    }

    /**
     * @return ImmutableArray<array-key, \App\Blog\BlogPost>
     */
    public function all(): ImmutableArray
    {
        event(new AllBlogPostsRetrieved());

        $posts = arr(glob(__DIR__ . "/Content/*.md"))
            ->map(function (string $path) {
                $content = file_get_contents($path);
                $cacheKey = crc32($content);

                return $this->cache->resolve($cacheKey, function () use ($path, $content) {
                    preg_match('/\d+-\d+-\d+-(?<slug>.*)\.md/', $path, $matches);
                    $frontMatter = YamlFrontMatter::parse($content)->matter();

                    $meta = [
                        'image' => uri([BlogController::class, 'metaPng'], slug: $matches['slug']),
                        ...($frontMatter['meta'] ?? []),
                    ];

                    unset($frontMatter['meta']);

                    return [
                        'slug' => $matches['slug'],
                        'date' => $this->parseDate($path),
                        'content' => $this->converter->convert($content)->getContent(),
                        'meta' => $meta,
                        ...$frontMatter,
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