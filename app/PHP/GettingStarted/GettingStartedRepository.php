<?php

namespace App\PHP\GettingStarted;

use App\Blog\Meta;
use Spatie\YamlFrontMatter\YamlFrontMatter;
use Tempest\Container\Tag;
use Tempest\Markdown\Markdown;
use Tempest\Support\Arr\ImmutableArray;
use function Tempest\Support\arr;
use function Tempest\Support\str;

final class GettingStartedRepository
{
    private static ?ImmutableArray $posts = null;

    public function __construct(
        #[Tag('php')] private readonly Markdown $markdown,
    ) {}

    public function find(string $category, string $slug): ?GettingStartedPage
    {
        $path = glob(__DIR__ . "/Content/*-{$category}/*-{$slug}.md")[0] ?? null;

        if (! $path) {
            return null;
        }

        $content = file_get_contents($path);

        if ($content === false) {
            return null;
        }

        $parsed = $this->markdown->parse($content);
        $frontMatter = $parsed->frontmatter;

        $meta = $frontMatter['meta'] ?? [];

        unset($frontMatter['meta'], $frontMatter['next']);

        preg_match('/(?<index>\d+)-(?<slug>.*)\.md/', $path, $matches);

        $page = new GettingStartedPage(
            index: (int)$matches['index'],
            slug: $slug,
            title: $frontMatter['title'] ?? str($slug)->replace('-', ' ')->upperFirst()->toString(),
            category: $category,
            content: $parsed->html,
            meta: new Meta(
                title: $meta['title'] ?? $frontMatter['title'] ?? null,
                description: $meta['description'] ?? $frontMatter['description'] ?? null,
//                image: uri([BlogController::class, 'metaPng'], slug: $slug),
                author: $meta['author'] ?? 'Brent Roose',
                canonical: $meta['canonical'] ?? null,
            ),
        );

        $allPages = $this->all();
        $currentIndex = null;

        foreach ($allPages as $i => $other) {
            if ($other->slug !== $slug) {
                continue;
            }

            $currentIndex = $i;
            break;
        }

        $page->next = $allPages[$currentIndex + 1] ?? null;

        return $page;
    }

    /**
     * @return ImmutableArray<array-key, \App\PHP\GettingStarted\GettingStartedPage>
     */
    public function all(): ImmutableArray
    {
        if (self::$posts !== null) {
            return self::$posts;
        }

        $posts = arr(glob(__DIR__ . '/Content/*/*.md'))
            ->filter(fn (string $path) => ! str_starts_with($path, __DIR__ . '/Content/_'))
            ->map(function (string $path) {
                $content = file_get_contents($path);
                preg_match('/(?<category>\d+-.*?)\/(?<index>\d+)-(?<slug>.*)\.md/', $path, $matches);

                $frontMatter = YamlFrontMatter::parse($content)->matter();

                $slug = $matches['slug'];

                $meta = $frontMatter['meta'] ?? [];

                unset($frontMatter['meta']);

                return new GettingStartedPage(
                    index: (int)$matches['index'],
                    slug: $slug,
                    title: $frontMatter['title'] ?? str($slug)->replace('-', ' ')->upperFirst()->toString(),
                    category: $matches['category'],
                    content: '',
                    meta: new Meta(
                        title: $meta['title'] ?? $frontMatter['title'] ?? null,
                        description: $meta['description'] ?? $frontMatter['description'] ?? null,
//                        image: uri([BlogController::class, 'metaPng'], slug: $slug),
                        author: $meta['author'] ?? null,
                        canonical: $meta['canonical'] ?? null,
                    ),
                );
            });

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
}