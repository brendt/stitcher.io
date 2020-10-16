<?php

namespace Brendt\Stitcher\Console\DTO;

use DateTimeImmutable;
use Illuminate\Support\Collection;

class Tweet
{
    public int $id;

    public DateTimeImmutable $date;

    public Collection $urls;

    public ?string $ownUrl;

    public string $description;

    public int $likes;

    public int $retweets;

    public static function make(object $data): self
    {
        $tweet = new self();

        $tweet->id = $data->id;

        $tweet->date = new DateTimeImmutable($data->created_at);

        $tweet->urls = collect($data->entities->urls ?? [])
            ->map(fn(object $url) => $url->expanded_url);

        $tweet->ownUrl = parse_url(
            $tweet->urls
                ->first(fn(string $url) => str_contains($url, 'stitcher.io')),
            PHP_URL_PATH
        );

        $tweet->description = $data->text;
        $tweet->likes = $data->favorite_count;
        $tweet->retweets = $data->retweet_count;

        return $tweet;
    }

    public function formattedDescription(): string
    {
        $description = str_replace(PHP_EOL, ' ', $this->description);

        if (strlen($description) < 40) {
            return $description;
        } else {
            $description = substr($description, 0, 39) . '…';
        }

        if ($this->date < new DateTimeImmutable('-1 week')) {
            return $description;
        }

        return "<options=underscore>{$description}</>";
    }

    public function formattedDate(): string
    {
        $dateAsString = $this->date->format('l, Y-m-d H:i');

        if ($this->date > new DateTimeImmutable('-4 weeks')) {
            return $dateAsString;
        }

        return "<bg=green;options=bold>{$dateAsString}</>";
    }

    public function formattedLikes(): string
    {
        if ($this->likes === 0) {
            $color = 'red;options=bold;fg=white';
        } elseif ($this->likes < 50) {
            return "⭐️ {$this->likes}";
        } elseif ($this->likes <= 100) {
            $color = 'yellow';
        } else {
            $color = 'green';
        }

        return "⭐️ <bg={$color}>{$this->likes}</>";
    }

    public function formattedRetweets(): string
    {
        if ($this->retweets === 0) {
            $color = 'red;options=bold;fg=white';
        } elseif ($this->retweets < 25) {
            return "🔁 {$this->retweets}";
        } elseif ($this->retweets <= 50) {
            $color = 'yellow';
        } else {
            $color = 'green';
        }

        return "🔁 <bg={$color}>{$this->retweets}</>";
    }
}
