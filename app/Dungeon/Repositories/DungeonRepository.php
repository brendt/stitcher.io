<?php

namespace App\Dungeon\Repositories;

use App\Dungeon\Dungeon;
use App\Support\Authentication\User;
use Tempest\Container\Singleton;
use Tempest\KeyValue\Redis\Redis;
use Throwable;

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

        try {
            set_error_handler(fn () => null);

            if (function_exists('igbinary_unserialize')) {
                $dungeon = igbinary_unserialize($payload);
            } else {
                $dungeon = unserialize($payload);
            }

            restore_error_handler();

            if (! $dungeon instanceof Dungeon) {
                return null;
            }

            return $dungeon;
        } catch (Throwable) {
            return null;
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