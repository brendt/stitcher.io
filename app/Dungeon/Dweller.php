<?php

namespace App\Dungeon;

final class Dweller
{
    public function __construct(
        public Point $point,
        public bool $isVisible = false,
    ) {}

    public function toArray(): array
    {
        return [
            'point' => $this->point,
            'isVisible' => $this->isVisible,
        ];
    }
}
