<?php

namespace App\Dungeon\Repositories;

use App\Dungeon\Dungeon;
use ErrorException;
use Tempest\Container\Singleton;
use Tempest\KeyValue\Redis\Redis;
use Throwable;

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

        $payload = $this->redis->get('dungeon');

        if (function_exists('igbinary_unserialize')) {
            return igbinary_unserialize($payload);
        } else {
            return unserialize($payload);
        }
    }

    public function persist(Dungeon $dungeon): void
    {
        if (function_exists('igbinary_serialize')) {
            $serialized = igbinary_serialize($dungeon);
        } else {
            $serialized = serialize($dungeon);
        }

        $this->redis->set('dungeon', $serialized);
    }
}