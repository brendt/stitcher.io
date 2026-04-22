<?php

namespace App\Dungeon;

final readonly class LakePoint
{
    public function __construct(
        public Point $point,
        public int $depth,
    ) {}
}