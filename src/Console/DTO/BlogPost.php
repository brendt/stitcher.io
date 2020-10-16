<?php

namespace Brendt\Stitcher\Console\DTO;

use DateTimeImmutable;

class BlogPost
{
    public string $url;

    public string $id;

    public DateTimeImmutable $date;

    public static function make(array $data, string $id): self
    {
        $blogPost = new self();

        $blogPost->id = $id;
        $blogPost->url = "/blog/{$id}";
        $blogPost->date = new DateTimeImmutable(date('Y-m-d H:i', $data['date']));

        return $blogPost;
    }

    public function urlEquals(string $url): bool
    {
        return $this->url === parse_url($url, PHP_URL_PATH);
    }
}
