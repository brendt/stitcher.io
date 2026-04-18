<?php

namespace App\Dungeon\Support;

use Exception;
use Random\Randomizer;

final readonly class RandomWithRandomizer implements Random
{
    public function __construct(private Randomizer $randomizer) {}

    public function chance(float $percentage): bool
    {
        return $this->randomizer->getInt(1, 100) <= ($percentage * 100);
    }

    public function item(array $items): mixed
    {
        $key = array_rand($items);

        return $items[$key] ?? throw new Exception('No random item found');
    }
}
