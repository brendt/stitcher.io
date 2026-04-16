<?php

namespace App\Dungeon\Repositories;

use App\Dungeon\Dungeon;
use Tempest\Container\Singleton;
use Tempest\KeyValue\Redis\Redis;

#[Singleton]
final readonly class DungeonRepository
{
    public function __construct(
        private Redis $redis,
    ) {}

    public function get(): ?Dungeon
    {
        if (! $this->redis->get("dungeon")) {
            return null;
        }

        return igbinary_unserialize($this->redis->get('dungeon'));
    }

    public function persist(Dungeon $dungeon): void
    {
        $serialized = igbinary_serialize($dungeon);

        $this->redis->set('dungeon', $serialized);
    }
}