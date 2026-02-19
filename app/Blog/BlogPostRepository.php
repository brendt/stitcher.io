<?php

namespace App\Blog;

use League\CommonMark\MarkdownConverter;
use Spatie\YamlFrontMatter\YamlFrontMatter;
use Tempest\Cache\Cache;
use Tempest\Container\Tag;
use Tempest\DateTime\DateTime;
use Tempest\Support\Arr\ImmutableArray;
use function Tempest\Router\uri;
use function Tempest\Support\arr;
use function Tempest\Support\str;

final  class BlogPostRepository
{
    private static ?ImmutableArray $posts = null;

    public function __construct(
        private readonly MarkdownConverter $converter,
        #[Tag('blog')] private readonly Cache $cache,
    ) {}

    public function find(string $slug): ?BlogPost
    {
        $path = glob(__DIR__ . "/Content/*{$slug}.md")[0] ?? null;

        if (! $path) {
            return null;
        }

        $cacheKey = hash('xxh64', $path);
        $lastModified = filemtime($path);

        $cachedVersion = $this->cache->get($cacheKey);

        if ($cachedVersion && $cachedVersion['lastModified'] === $lastModified) {
            return $cachedVersion['post'];
        }

        $content = file_get_contents($path);

        $frontMatter = YamlFrontMatter::parse($content)->matter();

        $meta = $frontMatter['meta'] ?? [];

        unset($frontMatter['meta'], $frontMatter['next']);

        $post = new BlogPost(
            slug: $slug,
            title: str($slug)->replace('-', ' ')->upperFirst()->toString(),
            content: $this->converter->convert($content)->getContent(),
            date: $this->parseDate($path),
            meta: new Meta(
                title: $meta['title'] ?? null,
                description: $meta['description'] ?? null,
                image: uri([BlogController::class, 'metaPng'], slug: $slug),
                author: $meta['author'] ?? null,
                canonical: $meta['canonical'] ?? null,
            ),
        );

        $allPosts = $this->all();
        $currentIndex = null;

        foreach ($allPosts as $i => $other) {
            if ($other->slug === $slug) {
                $currentIndex = $i;
                break;
            }
        }

        $post->next = $allPosts[$currentIndex + 1] ?? null;

        $this->cache->put($cacheKey, ['post' => $post, 'lastModified' => $lastModified]);

        return $post;
    }

    /**
     * @return ImmutableArray<array-key, BlogPost>
     */
    public function all(): ImmutableArray
    {
        if (self::$posts !== null) {
            return self::$posts;
        }

        $posts = arr(glob(__DIR__ . "/Content/*.md"))
            ->reverse()
            ->filter(fn (string $path) => ! str_starts_with($path, __DIR__ . '/Content/_'))
            ->map(function (string $path) {
                $content = file_get_contents($path);
                preg_match('/\d+-\d+-\d+-(?<slug>.*)\.md/', $path, $matches);
                $frontMatter = YamlFrontMatter::parse($content)->matter();

                $slug = $matches['slug'];

                $meta = $frontMatter['meta'] ?? [];

                unset($frontMatter['meta']);

                return new BlogPost(
                    slug: $slug,
                    title: str($slug)->replace('-', ' ')->upperFirst()->toString(),
                    content: '',
                    date: $this->parseDate($path),
                    meta: new Meta(
                        title: $meta['title'] ?? null,
                        description: $meta['description'] ?? null,
                        image: uri([BlogController::class, 'metaPng'], slug: $slug),
                        author: $meta['author'] ?? null,
                        canonical: $meta['canonical'] ?? null,
                    ),
                );
            })
            ->sortByCallback(fn (BlogPost $a, BlogPost $b) => $b->date <=> $a->date);

        foreach ($posts as $i => $post) {
            $next = $posts[$i + 1] ?? null;

            if (! $next) {
                continue;
            }

            $post->next = $next;
        }

        self::$posts = $posts;

        return self::$posts;
    }

    private function parseDate(string $path): DateTime
    {
        preg_match('#\d+-\d+-\d+#', $path, $matches);

        $date = $matches[0] ?? null;

        return DateTime::parse($date ?? 'now');
    }
}