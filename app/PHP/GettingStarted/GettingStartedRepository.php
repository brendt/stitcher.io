<?php

namespace App\PHP\GettingStarted;

use App\Blog\Meta;
use Generator;
use Spatie\YamlFrontMatter\YamlFrontMatter;
use Tempest\Container\Tag;
use Tempest\Markdown\Markdown;
use Tempest\Router\DataProvider;
use Tempest\Support\Arr\ImmutableArray;

use function Tempest\Router\uri;
use function Tempest\Support\arr;
use function Tempest\Support\str;

final class GettingStartedRepository implements DataProvider
{
    /** @var ImmutableArray<array-key, GettingStartedPage>|null */
    private static ?ImmutableArray $posts = null;

    public function __construct(
        #[Tag('php')]
        private readonly Markdown $markdown,
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

        if (! is_array($meta)) {
            $meta = [];
        }

        unset($frontMatter['meta'], $frontMatter['next']);

        $pageInfo = $this->pageInfoFromPath($path);

        $category = $pageInfo['category'];
        $categorySlug = str($category)->afterFirst('-')->toString();
        $title = $this->stringValue($frontMatter, 'title') ?? str($slug)->replace('-', ' ')->upperFirst()->toString();
        $url = uri(
            [GettingStartedController::class, 'show'],
            category: $categorySlug,
            slug: $slug,
        );

        $page = new GettingStartedPage(
            index: $pageInfo['index'],
            slug: $slug,
            title: $title,
            category: $category,
            content: $parsed->html,
            meta: new Meta(
                title: $this->stringValue($meta, 'title') ?? $title,
                description: $this->descriptionFor($meta, $frontMatter, $content),
                image: $this->stringValue($meta, 'image') ?? $frontMatter['image'] ?? uri('/meta/meta_lg.png'),
                author: $this->stringValue($meta, 'author') ?? 'Brent Roose',
                canonical: $this->stringValue($meta, 'canonical') ?? $url,
                uri: $url,
                type: 'article',
                keywords: ['PHP', 'learn PHP', 'PHP tutorial', 'modern PHP', $title],
                breadcrumbs: [
                    ['name' => 'Getting started with PHP', 'url' => uri('/php')],
                    ['name' => $title, 'url' => $url],
                ],
                jsonLd: [
                    'learningResourceType' => 'Tutorial',
                    'teaches' => $title,
                    'about' => [
                        ['@type' => 'Thing', 'name' => 'PHP'],
                    ],
                ],
            ),
        );

        $allPages = $this->all();
        $currentIndex = null;

        foreach ($allPages as $i => $other) {
            if ($other->slug !== $slug) {
                continue;
            }

            $currentIndex = (int) $i;
            break;
        }

        if ($currentIndex !== null) {
            $page->next = $allPages[$currentIndex + 1] ?? null;
            $page->previous = $allPages[$currentIndex - 1] ?? null;
        }

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
            ->filter(fn (string $path) => ! str_starts_with(pathinfo($path, PATHINFO_FILENAME), '_'))
            ->map(function (string $path): GettingStartedPage {
                $content = file_get_contents($path);

                if ($content === false) {
                    $content = '';
                }

                $pageInfo = $this->pageInfoFromPath($path);

                $frontMatter = YamlFrontMatter::parse($content)->matter();

                $slug = $pageInfo['slug'];
                $category = $pageInfo['category'];
                $categorySlug = str($category)->afterFirst('-')->toString();
                $title = $this->stringValue($frontMatter, 'title') ?? str($slug)->replace('-', ' ')->upperFirst()->toString();
                $uri = uri(
                    [GettingStartedController::class, 'show'],
                    category: $categorySlug,
                    slug: $slug,
                );

                $meta = $frontMatter['meta'] ?? [];

                if (! is_array($meta)) {
                    $meta = [];
                }

                return new GettingStartedPage(
                    index: $pageInfo['index'],
                    slug: $slug,
                    title: $title,
                    category: $category,
                    content: '',
                    meta: new Meta(
                        title: $this->stringValue($meta, 'title') ?? $title,
                        description: $this->descriptionFor($meta, $frontMatter, $content),
                        image: $this->stringValue($meta, 'image') ?? $frontMatter['image'] ?? uri('/meta/meta_lg.png'),
                        author: $this->stringValue($meta, 'author') ?? 'Brent Roose',
                        canonical: $this->stringValue($meta, 'canonical') ?? $uri,
                        uri: $uri,
                        type: 'article',
                        keywords: ['PHP', 'learn PHP', 'PHP tutorial', 'modern PHP', $title],
                        breadcrumbs: [
                            ['name' => 'Getting started with PHP', 'url' => uri('/php')],
                            ['name' => $title, 'url' => $uri],
                        ],
                        jsonLd: [
                            'learningResourceType' => 'Tutorial',
                            'teaches' => $title,
                            'about' => [
                                ['@type' => 'Thing', 'name' => 'PHP'],
                            ],
                        ],
                    ),
                );
            });

        foreach ($posts as $i => $post) {
            $index = (int) $i;
            $next = $posts[$index + 1] ?? null;
            $previous = $posts[$index - 1] ?? null;

            $post->next = $next;
            $post->previous = $previous;
        }

        self::$posts = $posts;

        return $posts;
    }

    public function provide(): Generator
    {
        foreach ($this->all() as $page) {
            yield [
                'category' => str($page->category)->afterFirst('-')->toString(),
                'slug' => $page->slug,
            ];
        }
    }

    /**
     * @return array{category: string, index: int, slug: string}
     */
    private function pageInfoFromPath(string $path): array
    {
        $matches = [];
        preg_match('/(?<category>\d+-.*?)\/(?<index>\d+)-(?<slug>.*)\.md/', $path, $matches);

        return [
            'category' => $matches['category'] ?? '',
            'index' => (int) ($matches['index'] ?? 0),
            'slug' => $matches['slug'] ?? '',
        ];
    }

    private function stringValue(array $data, string $key): ?string
    {
        $value = $data[$key] ?? null;

        return is_string($value) ? $value : null;
    }

    private function descriptionFor(array $meta, array $frontMatter, string $content): ?string
    {
        $description = $meta['description'] ?? $frontMatter['description'] ?? null;

        if (is_string($description)) {
            return $description;
        }

        $content = preg_replace('/\A---.*?---\s*/', '', $content) ?? $content;
        $paragraphs = preg_split('/\R{2,}/', trim($content)) ?: [];

        foreach ($paragraphs as $paragraph) {
            $paragraph = trim($paragraph);

            if ($paragraph === '' || str_starts_with($paragraph, '#') || str_starts_with($paragraph, '```') || str_starts_with($paragraph, '{{')) {
                continue;
            }

            $paragraph = preg_replace('/\[([^\]]+)]\([^)]+\)/', '$1', $paragraph) ?? $paragraph;
            $paragraph = preg_replace('/[`*_>#-]+/', '', $paragraph) ?? $paragraph;
            $paragraph = strip_tags($paragraph);
            $paragraph = preg_replace('/\s+/', ' ', $paragraph) ?? $paragraph;
            $paragraph = trim($paragraph);

            if (mb_strlen($paragraph) < 50) {
                continue;
            }

            if (mb_strlen($paragraph) <= 160) {
                return $paragraph;
            }

            return rtrim(mb_substr($paragraph, 0, 157), " \t\n\r\0\x0B.,;:") . '...';
        }

        return null;
    }
}
