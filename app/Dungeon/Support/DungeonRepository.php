<?php

namespace App\Dungeon\Support;

use App\Dungeon\Dungeon;
use Tempest\Cache\Cache;
use Tempest\Container\Singleton;

#[Singleton]
final readonly class DungeonRepository
{
    public function __construct(
        private Cache $cache,
    ) {}

    public function get(): ?Dungeon
    {
        if (! $this->cache->has('dungeon')) {
            return null;
        }

        return Dungeon::fromArray($this->cache->get('dungeon'));
    }

    public function persist(Dungeon $dungeon): void
    {
        $this->cache->put('dungeon', $dungeon->toArray());
    }
}