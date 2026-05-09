<?php

namespace App\Dungeon\Support;

final class CardConfig
{
    public function __construct(
        /** @var \App\Dungeon\Card[] */
        public array $cards = [],
    ) {}
}