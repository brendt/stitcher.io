<?php

namespace App\Dungeon\Repositories;

use App\Dungeon\Dungeon;
use App\Support\Authentication\User;
use Tempest\Container\Singleton;
use Tempest\KeyValue\Redis\Redis;

#[Singleton]
final readonly class DungeonRepository
{
    public function __construct(
        private Redis $redis,
    ) {}

    public function forUser(User $user): ?Dungeon
    {
        $key = "dungeon-{$user->id}";

        if (! $this->redis->get($key)) {
            return null;
        }

        $payload = $this->redis->get($key);

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

        $key = "dungeon-{$dungeon->user->id}";

        $this->redis->set($key, $serialized);
    }
}