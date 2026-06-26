<?php

namespace App\PHP\GettingStarted;

use App\Blog\Meta;
use function Tempest\Router\uri;
use function Tempest\Support\str;

final class GettingStartedPage
{
    public function __construct(
        public int $index,
        public string $slug,
        public string $title,
        public string $category,
        public string $content,
        public Meta $meta,
        public ?GettingStartedPage $next = null,
    ) {}

    public string $uri {
        get => uri(
            [GettingStartedController::class, 'show'],
            category: str($this->category)->afterFirst('-')->toString(),
            slug: $this->slug
        );
    }

    public string $categoryName {
        get => str($this->category)->replace('-', ' ')->title()->toString();
    }

    public array $sections {
        get {
            preg_match_all('/<h2 id="(?<id>.*?)">(?<title>.*?)<\/h2>/', $this->content, $matches);

            $sections = [];

            foreach ($matches[0] as $i => $_) {
                $sections['#' . $matches['id'][$i]] = $matches['title'][$i];
            }

            return $sections;
        }
    }
}