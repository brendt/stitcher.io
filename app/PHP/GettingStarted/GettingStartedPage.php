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
        public ?GettingStartedPage $previous = null,
    ) {}

    public string $uri {
        get => uri(
            [GettingStartedController::class, 'show'],
            category: str($this->category)->afterFirst('-')->toString(),
            slug: $this->slug,
        );
    }

    public string $categoryName {
        get => str($this->category)->afterFirst('-')->replace('-', ' ')->title()->toString();
    }

    public array $sections {
        get {
            /** @var array<int, array{id: string, title: string}> $matches */
            $matches = [];
            preg_match_all('/<h2 id="(?<id>.*?)">(?<title>.*?)<\/h2>/', $this->content, $matches, PREG_SET_ORDER);

            $sections = [];

            foreach ($matches as $match) {
                $sections['#' . $match['id']] = $match['title'];
            }

            return $sections;
        }
    }
}
