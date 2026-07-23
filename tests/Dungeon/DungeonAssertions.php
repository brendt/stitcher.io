<?php

namespace Tests\Dungeon;

use function Tempest\Testing\test;

trait DungeonAssertions
{
    protected function assertSame(mixed $expected, mixed $actual): void
    {
        test($actual)->is($expected);
    }

    protected function assertEqualsCanonicalizing(array $expected, array $actual): void
    {
        $normalize = static function (array $values): array {
            $values = array_map(serialize(...), $values);
            sort($values);

            return $values;
        };

        test($normalize($actual))->is($normalize($expected));
    }

    protected function assertNull(mixed $actual): void
    {
        test($actual)->isNull();
    }

    protected function assertNotNull(mixed $actual): void
    {
        test($actual)->isNotNull();
    }

    protected function assertTrue(mixed $actual): void
    {
        test($actual)->isTrue();
    }

    protected function assertFalse(mixed $actual): void
    {
        test($actual)->isFalse();
    }

    protected function assertEmpty(mixed $actual): void
    {
        test($actual)->isEmpty();
    }

    protected function assertArrayHasKey(mixed $key, array $actual): void
    {
        test($actual)->hasKey($key);
    }

    protected function assertArrayNotHasKey(mixed $key, array $actual): void
    {
        test($actual)->missesKey($key);
    }

    protected function assertGreaterThan(int|float $expected, int|float $actual): void
    {
        test($actual)->greaterThan($expected);
    }

    protected function assertLessThan(int|float $expected, int|float $actual): void
    {
        test($actual)->lessThan($expected);
    }

    protected function assertLessThanOrEqual(int|float $expected, int|float $actual): void
    {
        test($actual)->lessThanOrEqual($expected);
    }
}
