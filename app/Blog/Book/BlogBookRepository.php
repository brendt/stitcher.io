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
                'php-in-2019',
                'php-in-2020',
                'php-in-2021',
                'php-in-2022',
                'php-in-2023',
                'php-in-2024',
                'php-in-2025',
                'php-2026',
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
                'attribute-usage-in-top-php-packages',
                'php-enum-style-guide',
                'evolution-of-a-php-object',
                'php-reimagined-part-2',
                'thoughts-on-asymmetric-visibility',
                'all-i-want-for-christmas',
                'limited-by-committee',
                'procedurally-generated-game-in-php',
                'the-framework-that-gets-out-of-your-way',
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
                'my-wishlist-for-php-in-2026',
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
            'php' => [
                'php-generics-and-why-we-need-them',
                'optimised-uuids-in-mysql',
                'asynchronous-php',
                'what-php-can-be',
                'laravel-view-models',
                'laravel-view-models-vs-view-composers',
                'organise-by-domain',
                'array-merge-vs+',
                'new-in-php-73',
                'php-jit',
                'array-destructuring-with-list-in-php',
                'short-closures-in-php',
                'php-in-2019',
                'typed-properties-in-php-74',
                'preloading-in-php-74',
                'a-letter-to-the-php-team',
                'a-letter-to-the-php-team-reply-to-joe',
                'new-in-php-74',
                'php-preload-benchmarks',
                'php-in-2020',
                'enums-without-enums',
                'bitwise-booleans-in-php',
                'constructor-promotion-in-php-8',
                'jit-in-real-life-web-applications',
                'php-8-match-or-switch',
                'why-we-need-named-params-in-php',
                'shorthand-comparisons-in-php',
                'php-8-before-and-after',
                'php-8-named-arguments',
                'attributes-in-php-8',
                'php-8-jit-setup',
                'php-8-nullsafe-operator',
                'new-in-php-8',
                'php-reimagined',
                'a-storm-in-a-glass-of-water',
                'php-enums-before-php-81',
                'php-enums',
                'fibers-with-a-grain-of-salt',
                'php-in-2021',
                'parallel-php',
                'why-we-need-multi-line-short-closures-in-php',
                'what-about-config-builders',
                'what-about-request-classes',
                'cloning-readonly-properties-in-php-81',
                'named-arguments-and-variadic-functions',
                'php-81-readonly-properties',
                'php-81-new-in-initializers',
                'route-attributes',
                'new-in-php-81',
                'php-in-2022',
                'generics-in-php-1',
                'generics-in-php-2',
                'generics-in-php-3',
                'generics-in-php-4',
                'attribute-usage-in-top-php-packages',
                'php-enum-style-guide',
                'evolution-of-a-php-object',
                'deprecated-dynamic-properties-in-php-82',
                'php-reimagined-part-2',
                'thoughts-on-asymmetric-visibility',
                'readonly-classes-in-php-82',
                'deprecating-spatie-dto',
                'new-in-php-82',
                'all-i-want-for-christmas',
                'php-in-2023',
                'cloning-readonly-properties-in-php-83',
                'limited-by-committee',
                'procedurally-generated-game-in-php',
                'override-in-php-83',
                'new-in-php-83',
                'the-framework-that-gets-out-of-your-way',
                'a-syntax-highlighter-that-doesnt-suck',
                'building-a-custom-language-in-tempest-highlight',
                'testing-patterns',
                'php-in-2024',
                'tagged-singletons',
                'new-with-parentheses-php-84',
                'html-5-in-php-84',
                'array-find-in-php-84',
                'improved-lazy-loading',
                'php-84-at-least',
                'extends-vs-implements',
                'a-simple-approach-to-static-generation',
                'new-in-php-84',
                'static-websites-with-tempest',
                'request-objects-in-tempest',
                'tempest-discovery-explained',
                'php-version-stats-june-2025',
                'pipe-operator-in-php-85',
                'a-year-of-property-hooks',
                'readonly-or-private-set',
                'my-wishlist-for-php-in-2026',
                'game-changing-editions',
                'new-in-php-85',
                'php-2026',
                'open-source-strategies',
                'php-86-partial-function-application',
            ],
            'musings' => [
                'dont-get-stuck',
                'static_sites_vs_caching',
                'a-programmers-cognitive-load',
                'mastering-key-bindings',
                'where-a-curly-bracket-belongs',
                'responsive-images-done-right',
                'dependency-injection-for-beginners',
                'liskov-and-type-safety',
                'acquisition-by-giants',
                'service-locator-anti-pattern',
                'the-web-in-2045',
                'structuring-unstructured-data',
                'have-you-thought-about-casing',
                'comparing-dates',
                'announcing-aggregate',
                'craftsmen-know-their-tools',
                'a-project-at-spatie',
                'tests-and-types',
                'things-dependency-injection-is-not-about',
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
                'why-do-i-write',
                'rational-thinking',
                're-on-using-psr-abstractions',
                'my-ikea-clock',
                'birth-and-death-of-a-framework',
                'how-to-be-right-on-the-internet',
                'how-i-plan',
                'twitter-home-made-me-miserable',
                'its-your-fault',
                'dealing-with-dependencies',
                'strategies',
                'dealing-with-deprecations',
                'uncertainty-doubt-and-static-analysis',
                'you-cannot-find-me-on-mastodon',
                'tabs-are-better',
                'acronyms',
                'code-folding',
                'light-colour-schemes',
                'slashdash',
                'things-considered-harmful',
                'dont-be-clever',
                'is-a-or-acts-as',
                'i-dont-know',
                'passion-projects',
                'twitter-exit',
                'a-vocal-minority',
                'you-should',
                'its-all-just-text',
                'i-dont-code-the-way-i-used-to',
                'building-a-framework',
                'things-i-learned-writing-a-fiction-novel',
                'unfair-advantage',
                'theoretical-engineers',
                'whats-your-motivator',
                'vendor-locked',
                'reducing-code-motion',
                'sponsoring-open-source',
                'not-optional',
                'processing-11-million-rows',
                'ai-induced-skepticism',
                '11-million-rows-in-seconds',
                '100-million-row-challenge',
                'a-for-artificial',
                'dependency-hygiene',
            ]
        ];

        $collection = $collection ? ($collections[$collection] ?? null) : null;

        $ignore = [
//            'stitcher-alpha-4',
//            'stitcher-alpha-5',
//            'which-editor-to-choose',
//            'image_optimizers',
//            'array-objects-with-fixed-types',
//            'process-forks',
//            'object-oriented-generators',
//            'responsive-images-as-css-background',
//            'mysql-import-json-binary-character-set',
//            'mysql-query-logging',
//            'mysql-show-foreign-key-errors',
//            'visual-perception-of-code',
//            'share-a-blog-assertchris-io',
//            'share-a-blog-codingwriter-com',
//            'share-a-blog-betterwebtype-com',
//            'share-a-blog-sebastiandedeyne-com',
//            'solid-interfaces-and-final-rant-with-brent',
//            'what-are-objects-anyway-rant-with-brent',
//            'can-i-translate-your-blog',
//            'laravel-has-many-through',
//            'php-74-upgrade-mac',
//            'improvements-on-laravel-nova',
//            'type-system-in-php-survey',
//            'abstract-resources-in-laravel-nova',
//            'my-journey-into-event-sourcing',
//            'differences',
//            'annotations',
//            'front-line-php',
//            'php-8-upgrade-mac',
//            'thoughts-on-event-sourcing',
//            'the-road-to-php',
//            'php-81-performance-in-real-life',
//            'php-81-upgrade-mac',
//            'php-in-2021-video',
//            'clean-and-minimalistic-phpstorm',
//            'road-to-php-82',
//            'php-performance-across-versions',
//            'light-colour-schemes-are-better',
//            'uses',
//            'sponsors',
//            'why-curly-brackets-go-on-new-lines',
//            'my-10-favourite-php-functions',
//            'thank-you-kinsta',
//            'tagging-tempest-livestream',
//            'flooded-rss',
//            'simplest-plugin-support',
//            'stitcher-beta-1',
//            'performance-101-building-the-better-web',
//            'stitcher-beta-2',
//            'phpstorm-performance',
//            'tackling_responsive_images-part_1',
//            'tackling_responsive_images-part_2',
//            'phpstorm-tips-for-power-users',
//            'phpstorm-performance-issues-on-osx',
//            'eloquent-mysql-views',
//            'phpstorm-performance-october-2018',
//            'analytics-for-developers',
//            'laravel-queueable-actions',
//            'php-73-upgrade-mac',
//            'unsafe-sql-functions-in-laravel',
//            'starting-a-newsletter',
//            'starting-a-podcast',
//            'guest-posts',
//            'laravel-custom-relation-classes',
//            'array-chunk-in-php',
//            'php-8-in-8-code-blocks',
//            'the-ikea-effect',
//            'php-74-in-7-code-blocks',
//            'merging-multidimensional-arrays-in-php',
//            'what-is-array-plus-in-php',
//            'phpstorm-scopes',
//            'a-new-major-version-of-laravel-event-sourcing',
//            'php-81-before-and-after',
//            'php-81-in-8-code-blocks',
//            'generics-in-php-video',
//            'goodbye',
//            'stitcher-turns-5',
//            'php-82-in-8-code-blocks',
//            'php-82-upgrade-mac',
//            'php-annotated',
//            'upgrading-to-php-82',
//            'php-verse-2025',
//            'things-i-wish-i-knew',
//            'impact-charts',
        ];

        $data = arr([__DIR__ . '/2026-05-06-foreword.md', ...glob(__DIR__ . '/../Content/*.md')])
            ->filter(function (string $path) use ($ignore, $filter, $collection) {
                if ($filter && ! str_contains($path, $filter)) {
                    return false;
                }

                foreach ($ignore as $ignorePath) {
                    if (str_contains($path, $ignorePath)) {
                        return false;
                    }
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