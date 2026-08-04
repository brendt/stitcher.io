<?php

namespace App\Blog;

use Tempest\DateTime\DateTime;

use function Tempest\Router\uri;

final class BlogPost
{
    public function __construct(
        public string $slug,
        public string $title,
        public string $content,
        public DateTime $date,
        public Meta $meta,
        public ?BlogPost $next = null,
        public bool $showSections = false,
    ) {}

    public string $uri {
        get => uri([BlogController::class, 'show'], slug: $this->slug);
    }

    public array $sections {
        get {
            if (isset($this->sections)) {
                return $this->sections;
            }

            /** @var array<int, array{id: string, title: string}> $matches */
            $matches = [];
            preg_match_all('/<h2 id="(?<id>.*?)">(?<title>.*?)<\/h2>/', $this->content, $matches, PREG_SET_ORDER);

            $sections = [];

            foreach ($matches as $match) {
                $sections['#' . $match['id']] = \Tempest\Support\Str\strip_tags($match['title']);
            }

            $this->sections = $sections;

            return $this->sections;
        }
    }
}
