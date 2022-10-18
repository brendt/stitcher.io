<?php

namespace Brendt\Stitcher;

class LikeRepository
{
    private const PATH = __DIR__ . '/likes.json';

    private function __construct(private array $likes = [])
    {
    }

    public static function make(): self
    {
        if (! file_exists(self::PATH)) {
            touch(self::PATH);
        }

        return new self(
            json_decode(file_get_contents(self::PATH), true)
        );
    }

    public function persist(): self
    {
        file_put_contents(self::PATH, json_encode($this->likes));

        return $this;
    }

    public function add(string $slug, string $likeId): self
    {
        $this->likes[$slug][$likeId] = $likeId;

        return $this;
    }

    public function count(string $slug): int
    {
        return count($this->likes[$slug] ?? []);
    }

    public function hasLiked(string $slug, string $likeId): bool
    {
        return isset($this->likes[$slug][$likeId]);
    }

    public function remove(string $slug, string $likeId): self
    {
        unset($this->likes[$slug][$likeId]);

        return $this;
    }

    public function all(): array
    {
        return $this->likes;
    }
}
