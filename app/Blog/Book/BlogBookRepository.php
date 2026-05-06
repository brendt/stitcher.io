<?php

namespace App\Blog\Book;

use League\CommonMark\MarkdownConverter;
use Spatie\YamlFrontMatter\YamlFrontMatter;
use Tempest\Cache\Cache;
use Tempest\Container\Tag;
use Tempest\Support\Arr\ImmutableArray;
use function Tempest\Support\arr;
use function Tempest\Support\str;

final readonly class BlogBookRepository
{
    public function __construct(
        #[Tag('book')] private MarkdownConverter $markdown,
        private Cache $cache,
    ) {}

    /** @return ImmutableArray<array-key, \App\Blog\Book\Chapter> */
    public function all(?string $filter = null, ?string $collection = null): ImmutableArray
    {
        $collections = [
            'highlights' => [
                'foreword',
                'generics-in-php-1',
                'generics-in-php-2',
                'generics-in-php-3',
                'generics-in-php-4',
                'what-php-can-be',
                'organise-by-domain',
                'typed-properties-in-php-74',
                'preloading-in-php-74',
                'a-letter-to-the-php-team',
                'bitwise-booleans-in-php',
                'jit-in-real-life-web-applications',
                'why-we-need-named-params-in-php',
                'dont-get-stuck',
                'php-reimagined',
                'a-storm-in-a-glass-of-water',
                'fibers-with-a-grain-of-salt',
                'why-we-need-multi-line-short-closures-in-php',
                'what-about-config-builders',
                'what-about-request-classes',
                'route-attributes',
                'php-enum-style-guide',
                'evolution-of-a-php-object',
                'php-reimagined-part-2',
                'thoughts-on-asymmetric-visibility',
                'all-i-want-for-christmas',
                'limited-by-committee',
                'procedurally-generated-game-in-php',
                'a-syntax-highlighter-that-doesnt-suck',
                'testing-patterns',
                'tagged-singletons',
                'improved-lazy-loading',
                'extends-vs-implements',
                'a-simple-approach-to-static-generation',
                'static-websites-with-tempest',
                'request-objects-in-tempest',
                'tempest-discovery-explained',
                'a-year-of-property-hooks',
                'readonly-or-private-set',
                'game-changing-editions',
                'open-source-strategies',
                'a-programmers-cognitive-load',
                'where-a-curly-bracket-belongs',
                'responsive-images-done-right',
                'liskov-and-type-safety',
                'acquisition-by-giants',
                'service-locator-anti-pattern',
                'the-web-in-2045',
                'structuring-unstructured-data',
                'have-you-thought-about-casing',
                'comparing-dates',
                'craftsmen-know-their-tools',
                'a-project-at-spatie',
                'tests-and-types',
                'event-driven-php',
                'minor-versions-breaking-changes',
                'combining-event-sourcing-and-stateful-systems',
                'builders-and-architects-two-types-of-programmers',
                'braille-and-the-history-of-software',
                'the-case-for-transpiled-generics',
                'why-light-themes-are-better-according-to-science',
                'what-a-good-pr-looks-like',
                'when-i-lost-a-few-hundred-leads',
                'websites-like-star-wars',
                'dont-write-your-own-framework',
                'honesty',
                'what-event-sourcing-is-not-about',
                'opinion-driven-design',
                'an-event-driven-mindset',
                'optimistic-or-realistic-estimates',
                'we-dont-need-runtime-type-checks',
                'rational-thinking',
                're-on-using-psr-abstractions',
                'my-ikea-clock',
                'birth-and-death-of-a-framework',
                'its-your-fault',
                'dealing-with-dependencies',
                'strategies',
                'dealing-with-deprecations',
                'uncertainty-doubt-and-static-analysis',
                'tabs-are-better',
                'code-folding',
                'dont-be-clever',
                'i-dont-know',
                'passion-projects',
                'twitter-exit',
                'a-vocal-minority',
                'you-should',
                'its-all-just-text',
                'building-a-framework',
                'whats-your-motivator',
                'sponsoring-open-source',
                'not-optional',
                'processing-11-million-rows',
                'ai-induced-skepticism',
                '11-million-rows-in-seconds',
                'a-for-artificial',
                'dependency-hygiene',
                'in-closing',
            ],
        ];

        $collection = $collection ? ($collections[$collection] ?? null) : null;

        $data = arr([__DIR__ . '/2026-05-06-foreword.md', ...glob(__DIR__ . '/../Content/*.md')])
            ->filter(function (string $path) use ($filter, $collection) {
                if ($filter && ! str_contains($path, $filter)) {
                    return false;
                }

                if (str_starts_with($path, '_')) {
                    return false;
                }

                return true;
            })
            ->map(function (string $path) {
                $cacheKey = crc32($path);

                if ($this->cache->has($cacheKey)) {
                    return $this->cache->get($cacheKey);
                }

                preg_match('/(?<date>\d+-\d+-\d+)-(?<slug>.*)\.md/', $path, $matches);

                $content = $path
                        |> file_get_contents(...)
                        |> (fn ($x) => preg_replace('/\{\{ (ad|cta):.* }}/', '', $x));

                $frontMatter = YamlFrontMatter::parse($content)->matter();
                $markdown = $this->markdown->convert($content);

                $slug = $matches['slug'];

                $chapter = new Chapter(
                    slug: $slug,
                    index: $matches['date'],
                    title: $frontMatter['title'] ?? str($slug)->replace('-', ' ')->upperFirst()->toString(),
                    body: $markdown->getContent(),
                );

                $this->cache->put($cacheKey, $chapter);

                return $chapter;
            })
            ->filter()
            ->filter(function (Chapter $chapter) use ($collection) {
                if (! $collection) {
                    return true;
                }

                return in_array($chapter->slug, $collection);
            })
            ->filter(fn (Chapter $chapter) => trim($chapter->body) !== '')
            ->values();

        return $data;
    }

    public function wordCount(): int
    {
        return $this->all()
            ->map(fn (Chapter $chapter) => str_word_count($chapter->body))
            ->reduce(fn (int $total, int $count) => $total + $count, 0);
    }
}