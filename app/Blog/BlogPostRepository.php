<?php

namespace App\Blog;

use League\CommonMark\MarkdownConverter;
use Spatie\YamlFrontMatter\YamlFrontMatter;
use Tempest\DateTime\DateTime;
use Tempest\Support\Arr\ImmutableArray;
use function Tempest\Router\uri;
use function Tempest\Support\arr;
use function Tempest\Support\str;

final  class BlogPostRepository
{
    private ?ImmutableArray $posts = null ;

    public function __construct(
        private readonly MarkdownConverter $converter,
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
            'title' => str($slug)->replace('-', ' ')->upperFirst()->toString(),
            'date' => $this->parseDate($path),
            'content' => $this->converter->convert($content)->getContent(),
            'meta' => $meta,
            ...$frontMatter,
        ];

        $post = \Tempest\Mapper\map($data)->to(BlogPost::class);

        $allPosts = $this->all();
        $currentIndex = null;

        foreach ($allPosts as $i => $other) {
            if ($other->slug === $slug) {
                $currentIndex = $i;
                break;
            }
        }

        $post->next = $allPosts[$currentIndex + 1] ?? null;

        return $post;
    }

    /**
     * @return ImmutableArray<array-key, BlogPost>
     */
    public function all(): ImmutableArray
    {
        if ($this->posts !== null) {
            return $this->posts;
        }

        $posts = arr(glob(__DIR__ . "/Content/*.md"))
            ->reverse()
            ->filter(fn (string $path) => ! str_starts_with($path, __DIR__ . '/Content/_'))
            ->map(function (string $path) {
                $content = file_get_contents($path);
                preg_match('/\d+-\d+-\d+-(?<slug>.*)\.md/', $path, $matches);
                $frontMatter = YamlFrontMatter::parse($content)->matter();

                $slug = $matches['slug'];

                $meta = [
                    'image' => uri([BlogController::class, 'metaPng'], slug: $slug),
                    ...($frontMatter['meta'] ?? []),
                ];

                unset($frontMatter['meta']);

                return [
                    'slug' => $slug,
                    'title' => str($slug)->replace('-', ' ')->upperFirst()->toString(),
                    'date' => $this->parseDate($path),
                    'meta' => $meta,
                    ...$frontMatter,
                ];
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

        $this->posts = $posts;

        return $this->posts;
    }

    private function parseDate(string $path): DateTime
    {
        preg_match('#\d+-\d+-\d+#', $path, $matches);

        $date = $matches[0] ?? null;

        return DateTime::parse($date ?? 'now');
    }
}