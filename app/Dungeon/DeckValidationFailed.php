<?php

namespace App\Dungeon;

final readonly class DeckValidationFailed
{
    public function __construct(
        public string $message,
    ) {}
}