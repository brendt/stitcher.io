<?php

namespace Brendt\Stitcher\Console\DTO;

use DateTimeImmutable;

class RedditPost
{
    public string $url;

    public string $domain;

    public string $title;

    public string $subreddit;

    public int $score;

    public DateTimeImmutable $date;

    public string $gilded;

    public bool $isOwn;

    public static function make(array $item): self
    {
        $date = new DateTimeImmutable(date('Y-m-d H:i:s', $item['created']));

        $submission = new self();

        $submission->url = (string) parse_url($item['url'], PHP_URL_PATH);
        $submission->domain = $item['domain'];
        $submission->title = $item['title'];
        $submission->subreddit = $item['subreddit_name_prefixed'];
        $submission->score = $item['score'];
        $submission->date = $date;
        $submission->gilded = str_repeat('⭐️', count($item['gildings']));
        $submission->isOwn = str_contains($submission->domain, 'stitcher.io');

        return $submission;
    }

    public function formattedTitle(): string
    {
        if (strlen($this->title) < 40) {
            $title = $this->title;
        } else {
            $title = substr($this->title, 0, 39) . '…';
        }

        if ($this->date < new DateTimeImmutable('-1 week')) {
            return $title;
        }

        return "<options=underscore>{$title}</>";
    }

    public function formattedScore(): string
    {
        return trim("{$this->gilded} " . $this->formatScore($this->score));
    }

    public function formattedDate(): string
    {
        $dateAsString = $this->date->format('Y-m-d H:i');

        if ($this->date > new DateTimeImmutable('-1 year')) {
            return $dateAsString;
        }

        return "<bg=green;options=bold>{$dateAsString}</>";
    }

    private function formatScore(int $score): string
    {
        if ($score === 0) {
            $color = 'red;options=bold;fg=white';
        } elseif ($score < 25) {
            return "{$score}";
        } elseif ($score <= 50) {
            $color = 'yellow';
        } else {
            $color = 'green';
        }

        return "<bg={$color}>{$score}</>";
    }
}
