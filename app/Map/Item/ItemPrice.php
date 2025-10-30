<?php

namespace App\Map\Item;

final class ItemPrice
{
    public function __construct(
        public int $wood = 0,
        public int $gold = 0,
        public int $stone = 0,
        public int $fish = 0,
        public int $flax = 0,
    ) {}

    public function __toString(): string
    {
        $items = [];

        foreach ((array) $this as $type => $price) {
            if ($price === 0) {
                continue;
            }

            $items[] = "{$price} {$type}";
        }

        return '(' . implode(', ', $items) . ')';
    }
}
